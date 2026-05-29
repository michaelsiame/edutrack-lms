<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find and drop existing FK on student_id
        $fk = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'quiz_attempts' 
            AND COLUMN_NAME = 'student_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if (!empty($fk)) {
            Schema::table('quiz_attempts', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk[0]->CONSTRAINT_NAME);
            });
        }

        // Recreate FK pointing to students table
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $fk = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'quiz_attempts' 
            AND COLUMN_NAME = 'student_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if (!empty($fk)) {
            Schema::table('quiz_attempts', function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk[0]->CONSTRAINT_NAME);
            });
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
