<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Policy: every quiz allows at least 2 retries (3 attempts total), and shows
 * the correct answers only once the student has passed or used all attempts
 * (the reveal gate lives in QuizController::showAttempt). Here we just ensure
 * the columns support that: at least 3 attempts, and answers allowed to show.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('quizzes')->where(function ($q) {
            $q->whereNull('max_attempts')->orWhere('max_attempts', '<', 3);
        })->update(['max_attempts' => 3]);

        DB::table('quizzes')->update(['show_correct_answers' => 1]);
    }

    public function down(): void
    {
        // Policy change; not reversible.
    }
};
