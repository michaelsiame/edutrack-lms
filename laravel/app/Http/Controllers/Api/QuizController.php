<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        if (!$quiz->is_published) {
            return response()->json(['success' => false, 'message' => 'Quiz not available'], 403);
        }

        $quiz->load(['questions.options', 'course']);

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

        // Check enrollment
        if (!$user->isEnrolledIn($quiz->course_id)) {
            return response()->json(['success' => false, 'message' => 'Not enrolled'], 403);
        }

        // Get next attempt number
        $lastAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $user->id)
            ->max('attempt_number') ?? 0;

        if ($lastAttempt >= $quiz->max_attempts) {
            return response()->json(['success' => false, 'message' => 'Max attempts reached'], 422);
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'attempt_number' => $lastAttempt + 1,
            'started_at' => now(),
            'status' => 'In Progress',
            'ip_address' => $request->ip(),
        ]);

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

        $attempt = QuizAttempt::where('id', $request->attempt_id)
            ->where('student_id', auth()->id())
            ->where('status', 'In Progress')
            ->firstOrFail();

        $score = 0;
        $totalPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $answer = $request->answers[$question->question_id] ?? null;

            if ($answer) {
                $isCorrect = false;
                $pointsEarned = 0;

                if ($question->question_type === 'Multiple Choice' || $question->question_type === 'True/False') {
                    $correctOption = $question->options()->where('is_correct', true)->first();
                    if ($correctOption && $correctOption->id == $answer) {
                        $isCorrect = true;
                        $pointsEarned = $question->points;
                    }
                }

                \App\Models\QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->question_id,
                    'selected_option_id' => is_numeric($answer) ? $answer : null,
                    'answer_text' => is_string($answer) ? $answer : null,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                $score += $pointsEarned;
            }
        }

        $percentage = $totalPoints > 0 ? ($score / $totalPoints) * 100 : 0;

        $attempt->update([
            'submitted_at' => now(),
            'completed_at' => now(),
            'score' => $percentage,
            'status' => 'Submitted',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'score' => $percentage,
                'passed' => $percentage >= $quiz->passing_score,
                'attempt' => $attempt,
            ],
        ]);
    }
}
