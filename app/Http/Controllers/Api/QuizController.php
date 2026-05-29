<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        if (!$quiz->is_published) {
            return response()->json(['success' => false, 'message' => 'Quiz not available'], 403);
        }

        $user = auth()->user();
        $enrollment = $user->enrollments()
            ->where('course_id', $quiz->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $quiz->load(['questions' => function($q) {
            $q->select('question_id', 'quiz_id', 'question_type', 'question_text', 'points');
        }, 'questions.options' => function($q) {
            $q->select('id', 'question_id', 'option_text'); // hide is_correct
        }, 'course']);

        return response()->json([
            'success' => true,
            'data' => $quiz,
        ]);
    }

    public function attempt(Request $request, Quiz $quiz)
    {
        if (!$quiz->is_published) {
            return response()->json(['success' => false, 'message' => 'Quiz not available'], 403);
        }

        $user = auth()->user();

        // Check enrollment and payment
        $enrollment = $user->enrollments()
            ->where('course_id', $quiz->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $studentId = $user->student?->id;

        // Check max attempts (count only completed attempts)
        $completedAttemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $studentId)
            ->whereIn('status', ['Graded', 'Submitted'])
            ->count();

        if ($quiz->max_attempts && $completedAttemptCount >= $quiz->max_attempts) {
            return response()->json(['success' => false, 'message' => 'Max attempts reached'], 422);
        }

        // Reuse existing In Progress attempt
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
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $attempt,
        ]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $request->validate([
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'answers' => 'required|array',
        ]);

        $user = auth()->user();

        // Check enrollment and payment
        $enrollment = $user->enrollments()
            ->where('course_id', $quiz->course_id)
            ->first();

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        if (!$enrollment->canAccessContent()) {
            return response()->json(['success' => false, 'message' => 'Payment required. Minimum 30% deposit needed.'], 403);
        }

        $attempt = QuizAttempt::where('id', $request->attempt_id)
            ->where('quiz_id', $quiz->id)
            ->where('student_id', auth()->user()->student?->id)
            ->where('status', 'In Progress')
            ->firstOrFail();

        $quiz->load('questions.options');

        $answers = $request->answers;
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

        return response()->json([
            'success' => true,
            'data' => [
                'score' => $score,
                'passed' => $score >= $quiz->passing_score,
                'attempt' => $attempt,
            ],
        ]);
    }
}
