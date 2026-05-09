<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            $table->enum('role', ['Lead', 'Assistant', 'Guest', 'Mentor'])->default('Lead');
            $table->date('assigned_date');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['course_id', 'instructor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
    }
};
