<?php

namespace Database\Seeders\Content;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Removes legacy orphan quizzes wrongly attached to the Mobile App Development
 * course (course_id 8). These came from an old database dump (pre content
 * seeders): 10 "Cybersecurity"-titled quiz shells with no questions, no
 * attempts, and no lesson link — they never belonged to a mobile-dev course.
 *
 * Surgical + idempotent: only deletes quizzes on course 8 that have ZERO
 * questions AND ZERO attempts, so real quizzes (authored later, with questions)
 * are never touched. Safe to run repeatedly and on production.
 */
class MobileAppOrphanQuizCleanupSeeder extends Seeder
{
    public function run(): void
    {
        $orphans = Quiz::where('course_id', 8)
            ->whereDoesntHave('questions')
            ->get()
            ->filter(fn (Quiz $q) => QuizAttempt::where('quiz_id', $q->id)->count() === 0);

        if ($orphans->isEmpty()) {
            $this->command->info('No orphan quizzes on course 8 — nothing to clean.');
            return;
        }

        DB::transaction(function () use ($orphans) {
            foreach ($orphans as $quiz) {
                $this->command->warn("Deleting orphan quiz #{$quiz->id}: {$quiz->title}");
                $quiz->delete();
            }
        });

        $this->command->info("Removed {$orphans->count()} orphan quiz(zes) from Mobile App Development.");
    }
}
