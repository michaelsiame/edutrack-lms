<?php
namespace App\Services;

use App\Models\Enrollment;

class GradeAggregationService
{
    public function recalculateFinalGrade(Enrollment $enrollment): void
    {
        $quizScore = $this->getBestQuizScore($enrollment);
        $assignmentScore = $this->getBestAssignmentScore($enrollment);
        
        $hasQuiz = !is_null($quizScore);
        $hasAssignment = !is_null($assignmentScore);
        
        if ($hasQuiz && $hasAssignment) {
            $finalGrade = ($quizScore + $assignmentScore) / 2;
        } elseif ($hasQuiz) {
            $finalGrade = $quizScore;
        } elseif ($hasAssignment) {
            $finalGrade = $assignmentScore;
        } else {
            return; // No gradable work yet
        }
        
        $enrollment->update(['final_grade' => round($finalGrade, 2)]);
    }
    
    private function getBestQuizScore(Enrollment $enrollment): ?float
    {
        $bestScore = \App\Models\QuizAttempt::whereHas('quiz', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })
            ->where('student_id', $enrollment->student_id)
            ->whereIn('status', ['Graded', 'Submitted'])
            ->max('score');
        
        return $bestScore ? round($bestScore, 2) : null;
    }
    
    private function getBestAssignmentScore(Enrollment $enrollment): ?float
    {
        $submission = \App\Models\AssignmentSubmission::whereHas('assignment', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })
            ->where('student_id', $enrollment->student_id)
            ->whereNotNull('points_earned')
            ->latest('submitted_at')
            ->first();
        
        if (!$submission) return null;
        
        $maxPoints = $submission->assignment->max_points ?? 100;
        return $maxPoints > 0 ? round(($submission->points_earned / $maxPoints) * 100, 2) : null;
    }
}
