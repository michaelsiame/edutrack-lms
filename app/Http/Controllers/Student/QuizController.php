<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    private function studentId(): int
    {
        $id = auth()->user()->student?->id;
        if (!$id) {
            abort(403, 'Student record not found.');
        }
        return $id;
    }

    public function index()
    {
        $enrollments = auth()->user()->enrollments()
            ->where('enrollment_status', '!=', 'Dropped')
            ->with('course.quizzes')
            ->get();

        $studentId = $this->studentId();

        // Collect all quiz IDs to load attempts in a single query (avoids N+1)
        $quizIds = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->quizzes as $quiz) {
                if ($quiz->is_published) {
                    $quizIds[] = $quiz->id;
                }
            }
        }

        $allAttempts = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->where('student_id', $studentId)
            ->orderBy('attempt_number')
            ->get()
            ->groupBy('quiz_id');

        $quizData = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->quizzes as $quiz) {
                if (!$quiz->is_published) continue;
                $attempts = $allAttempts->get($quiz->id, collect());
                $completedAttempts = $attempts->whereIn('status', ['Graded', 'Submitted']);

                $quizData[] = [
                    'quiz' => $quiz,
                    'course' => $enrollment->course,
                    'attempts' => $attempts,
                    'best_score' => $attempts->max('score'),
                    'attempts_count' => $attempts->count(),
                    'can_retake' => !$quiz->max_attempts || $completedAttempts->count() < $quiz->max_attempts,
                ];
            }
        }

        return view('student.quizzes.index', compact('quizData'));
    }

    public function attempts(Quiz $quiz)
    {
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', '!=', 'Dropped')
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            abort(403, 'Please complete at least a 30% deposit to view quiz attempts.');
        }

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $this->studentId())
            ->with(['answers.question.options'])
            ->orderBy('attempt_number')
            ->get();

        return view('student.quiz-attempts.index', compact('quiz', 'attempts'));
    }

    public function showAttempt(QuizAttempt $attempt)
    {
        if ($attempt->student_id !== $this->studentId()) {
            abort(403);
        }

        $attempt->load(['quiz.course', 'answers.question.options']);

        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $attempt->quiz->course_id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            abort(403, 'Please complete at least a 30% deposit to view quiz attempts.');
        }

        return view('student.quiz-attempts.show', compact('attempt'));
    }

    public function take(Quiz $quiz)
    {
        // Verify enrollment in the quiz's course
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            return redirect()->route('checkout.show', $quiz->course)
                ->with('warning', 'Please complete at least a 30% deposit to take quizzes.');
        }

        if (!$quiz->is_published) {
            abort(404);
        }

        $quiz->load(['questions.options', 'course']);
        $questions = $quiz->questions;

        // Check max attempts (count only completed attempts)
        $completedAttemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $this->studentId())
            ->whereIn('status', ['Graded', 'Submitted'])
            ->count();

        if ($quiz->max_attempts && $completedAttemptCount >= $quiz->max_attempts) {
            return redirect()->route('student.quizzes.attempts', $quiz)
                ->with('error', 'You have reached the maximum number of attempts for this quiz.');
        }

        // Find or create an in-progress attempt to track start time
        $studentId = $this->studentId();

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $studentId)
            ->where('status', 'In Progress')
            ->first();

        if (!$attempt) {
            // Use a transaction + pessimistic lock to prevent race condition
            // on attempt_number generation
            $attempt = DB::transaction(function () use ($quiz, $studentId) {
                $nextAttemptNumber = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $studentId)
                    ->lockForUpdate()
                    ->max('attempt_number') + 1;

                return QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'student_id' => $studentId,
                    'attempt_number' => $nextAttemptNumber,
                    'started_at' => now(),
                    'status' => 'In Progress',
                    'ip_address' => request()->ip(),
                ]);
            });
        }

        $remainingSeconds = null;
        if ($quiz->time_limit_minutes) {
            $elapsed = now()->timestamp - $attempt->started_at->timestamp;
            $remainingSeconds = max(0, ($quiz->time_limit_minutes * 60) - $elapsed);

            if ($remainingSeconds <= 0) {
                // Time expired — auto-submit empty attempt
                $attempt->update([
                    'submitted_at' => now(),
                    'completed_at' => now(),
                    'status' => 'Graded',
                    'score' => 0,
                    'time_spent_minutes' => $quiz->time_limit_minutes,
                ]);
                return redirect()->route('student.quizzes.attempts', $quiz)
                    ->with('error', 'Your time has expired. The quiz was submitted automatically.');
            }
        }

        return view('student.learning.quiz', compact('quiz', 'questions', 'attempt', 'remainingSeconds'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('student.quizzes.take', $quiz);
        }

        // Verify enrollment
        $enrollment = auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

        if (!$enrollment->canAccessContent()) {
            abort(403, 'Please complete at least a 30% deposit to submit quizzes.');
        }

        $quiz->load('questions.options');

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'attempt_id' => ['required', 'integer'],
        ]);

        $attempt = QuizAttempt::where('id', $validated['attempt_id'])
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $this->studentId())
            ->where('status', 'In Progress')
            ->firstOrFail();

        $timeSpent = round($attempt->started_at->diffInMinutes(now()));
        $elapsedSeconds = $attempt->started_at->diffInSeconds(now());
        if ($quiz->time_limit_minutes && $elapsedSeconds > ($quiz->time_limit_minutes * 60) + 60) {
            $attempt->update(['status' => 'Abandoned', 'score' => 0]);
            return redirect()->route('student.quizzes.attempts', $quiz)
                ->with('error', 'Time limit exceeded. Your attempt has been abandoned.');
        }

        $answers = $validated['answers'];
        $totalPoints = 0;
        $earnedPoints = 0;
        $hasEssay = false;

        DB::transaction(function () use ($attempt, $quiz, $answers, $timeSpent, &$totalPoints, &$earnedPoints, &$hasEssay, $enrollment) {
            // Clear any existing answers for this attempt (in case of resubmit)
            QuizAnswer::where('attempt_id', $attempt->id)->delete();

            foreach ($quiz->questions as $question) {
                $totalPoints += $question->points;
                $answerValue = $answers[$question->question_id] ?? null;
                $isCorrect = false;
                $pointsEarned = 0;
                $selectedOptionId = null;
                $answerText = null;

                if ($question->question_type === 'Multiple Choice') {
                    $selectedOptionId = is_numeric($answerValue) ? (int) $answerValue : null;
                    if ($selectedOptionId) {
                        $correctOption = $question->options->firstWhere('is_correct', true);
                        $isCorrect = $correctOption && $correctOption->id == $selectedOptionId;
                    }
                } elseif ($question->question_type === 'True/False') {
                    $answerText = $answerValue;
                    $correctOption = $question->options->firstWhere('is_correct', true);
                    $isCorrect = $correctOption && strtolower($correctOption->option_text) === strtolower($answerValue);
                } elseif ($question->question_type === 'Short Answer' || $question->question_type === 'Fill in Blank') {
                    $answerText = $answerValue;
                    if ($question->correct_answer && $answerValue) {
                        $isCorrect = strtolower(trim($answerValue)) === strtolower(trim($question->correct_answer));
                    }
                } elseif ($question->question_type === 'Essay') {
                    $answerText = $answerValue;
                    $hasEssay = true;
                    // Essays are manually graded - don't auto-mark as correct
                    $isCorrect = false;
                    $pointsEarned = 0;
                }

                if ($question->question_type !== 'Essay') {
                    $pointsEarned = $isCorrect ? $question->points : 0;
                }
                $earnedPoints += $pointsEarned;

                QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->question_id,
                    'selected_option_id' => $selectedOptionId,
                    'answer_text' => $answerText,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);
            }

            $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

            $attempt->update([
                'submitted_at' => now(),
                'completed_at' => now(),
                'status' => $hasEssay ? 'Submitted' : 'Graded',
                'score' => $score,
                'time_spent_minutes' => $timeSpent,
            ]);

            app(\App\Services\GradeAggregationService::class)->recalculateFinalGrade($enrollment);
        });

        return redirect()->route('student.quizzes.attempt', $attempt);
    }
}
