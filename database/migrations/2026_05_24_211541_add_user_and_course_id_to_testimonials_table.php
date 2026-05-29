<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            // Drop if already exist from previous failed attempt
            if (Schema::hasColumn('testimonials', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('testimonials', 'course_id')) {
                $table->dropColumn('course_id');
            }
            if (Schema::hasColumn('testimonials', 'enrollment_id')) {
                $table->dropColumn('enrollment_id');
            }
        });

        Schema::table('testimonials', function (Blueprint $table) {
            // Use signed integer to match existing id columns (int(11), NOT unsigned)
            $table->integer('user_id')->nullable()->after('submitted_by');
            $table->integer('course_id')->nullable()->after('user_id');
            $table->integer('enrollment_id')->nullable()->after('course_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->onDelete('set null');

            $table->unique(['user_id', 'enrollment_id'], 'testimonials_user_enrollment_unique');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropUnique('testimonials_user_enrollment_unique');
            $table->dropForeign(['user_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['enrollment_id']);
            $table->dropColumn(['user_id', 'course_id', 'enrollment_id']);
        });
    }
};
