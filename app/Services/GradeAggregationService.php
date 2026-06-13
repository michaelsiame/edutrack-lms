<?php
namespace App\Services;

use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\QuizAttempt;

class GradeAggregationService
{
    /**
     * Recalculate an enrolment's final grade across ALL assessments.
     *
     * Quiz component  = average of the best attempt for each attempted quiz.
     * Assignment comp = average of the best submission for each graded assignment.
     * Final grade     = the two components combined by the configured weights
     *                   (quizzes vs assignments). When a course has only one
     *                   kind of assessment, that component is the whole grade.
     */
    public function recalculateFinalGrade(Enrollment $enrollment): void
    {
        $quizComponent = $this->quizComponent($enrollment);
        $assignmentComponent = $this->assignmentComponent($enrollment);

        if ($quizComponent !== null && $assignmentComponent !== null) {
            $qw = (float) config('edutrack.grade.quiz_weight', 40);
            $aw = (float) config('edutrack.grade.assignment_weight', 60);
            $totalWeight = $qw + $aw;

            $finalGrade = $totalWeight > 0
                ? ($quizComponent * $qw + $assignmentComponent * $aw) / $totalWeight
                : ($quizComponent + $assignmentComponent) / 2;
        } elseif ($quizComponent !== null) {
            $finalGrade = $quizComponent;
        } elseif ($assignmentComponent !== null) {
            $finalGrade = $assignmentComponent;
        } else {
            return; // No gradable work yet
        }

        $enrollment->update(['final_grade' => round($finalGrade, 2)]);
    }

    /**
     * Average of the best attempt percentage for each quiz the student has
     * attempted in this course. Quiz scores are already stored as percentages.
     */
    private function quizComponent(Enrollment $enrollment): ?float
    {
        $bestPerQuiz = QuizAttempt::whereHas('quiz', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })
            ->where('student_id', $enrollment->student_id)
            ->whereIn('status', ['Graded', 'Submitted'])
            ->selectRaw('quiz_id, MAX(score) as best')
            ->groupBy('quiz_id')
            ->pluck('best');

        return $bestPerQuiz->isEmpty() ? null : round($bestPerQuiz->avg(), 2);
    }

    /**
     * Average of the best submission percentage for each assignment the student
     * has had graded in this course (points_earned / max_points * 100).
     */
    private function assignmentComponent(Enrollment $enrollment): ?float
    {
        $submissions = AssignmentSubmission::whereHas('assignment', function ($q) use ($enrollment) {
                $q->where('course_id', $enrollment->course_id);
            })
            ->where('student_id', $enrollment->student_id)
            ->whereNotNull('points_earned')
            ->with('assignment:id,max_points')
            ->get();

        if ($submissions->isEmpty()) {
            return null;
        }

        $bestPerAssignment = $submissions
            ->groupBy('assignment_id')
            ->map(function ($group) {
                return $group->max(function ($s) {
                    $max = $s->assignment->max_points ?? 100;
                    return $max > 0 ? ($s->points_earned / $max) * 100 : 0;
                });
            });

        return round($bestPerAssignment->avg(), 2);
    }
}
