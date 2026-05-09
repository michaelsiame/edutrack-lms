<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->integer('max_points')->default(100);
            $table->integer('passing_points')->default(60);
            $table->timestamp('due_date')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->decimal('late_penalty_percent', 5, 2)->default(0.00);
            $table->integer('max_file_size_mb')->default(10);
            $table->string('allowed_file_types', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
