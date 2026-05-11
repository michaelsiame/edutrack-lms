<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function take(Quiz $quiz)
    {
        // Verify enrollment in the quiz's course
        auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

        $quiz->load(['questions.options', 'course']);

        // Check max attempts
        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', auth()->id())
            ->count();

        if ($quiz->max_attempts && $attemptCount >= $quiz->max_attempts) {
            return back()->with('error', 'You have reached the maximum number of attempts for this quiz.');
        }

        return view('student.learning.quiz', compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        // Verify enrollment
        auth()->user()->enrollments()
            ->where('course_id', $quiz->course_id)
            ->firstOrFail();

        $quiz->load('questions.options');

        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $answers = $validated['answers'];
        $totalPoints = 0;
        $earnedPoints = 0;

        // Create attempt record
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => auth()->id(),
            'attempt_number' => QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', auth()->id())->count() + 1,
            'started_at' => now(),
            'submitted_at' => now(),
            'status' => 'completed',
        ]);

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
            } else {
                $answerText = $answerValue;
                // Short answer / essay - manual grading, mark as pending
                $isCorrect = false;
            }

            $pointsEarned = $isCorrect ? $question->points : 0;
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
            'score' => $score,
            'completed_at' => now(),
        ]);

        $correctCount = QuizAnswer::where('attempt_id', $attempt->id)->where('is_correct', true)->count();
        $totalQuestions = $quiz->questions->count();

        return view('student.learning.quiz_result', compact('quiz', 'attempt', 'score', 'correctCount', 'totalQuestions'));
    }
}
