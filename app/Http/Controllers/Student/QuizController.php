<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

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

        $quizData = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->course->quizzes as $quiz) {
                $attempts = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $this->studentId())
                    ->orderBy('attempt_number')
                    ->get();

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
        auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->where('enrollment_status', '!=', 'Dropped')
            ->firstOrFail();

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

        return view('student.quiz-attempts.show', compact('attempt'));
    }

    public function take(Quiz $quiz)
    {
        // Verify enrollment in the quiz's course
        auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

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
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $studentId,
                'attempt_number' => QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('student_id', $studentId)
                    ->count() + 1,
                'started_at' => now(),
                'status' => 'In Progress',
                'ip_address' => request()->ip(),
            ]);
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
        auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

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

        $answers = $validated['answers'];
        $totalPoints = 0;
        $earnedPoints = 0;
        $hasEssay = false;

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
        $timeSpent = round($attempt->started_at->diffInMinutes(now()));

        $attempt->update([
            'submitted_at' => now(),
            'completed_at' => now(),
            'status' => $hasEssay ? 'Submitted' : 'Graded',
            'score' => $score,
            'time_spent_minutes' => $timeSpent,
        ]);

        return redirect()->route('student.quizzes.attempt', $attempt);
    }
}
