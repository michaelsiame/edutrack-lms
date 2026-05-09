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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 250)->unique();
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->foreignId('category_id')->constrained('course_categories');
            $table->foreignId('instructor_id')->nullable()->constrained('instructors');
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
            $table->string('language', 50)->default('English');
            $table->string('thumbnail_url', 255)->nullable();
            $table->string('video_intro_url', 255)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('duration_weeks')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->integer('max_students')->default(30);
            $table->integer('enrollment_count')->default(0);
            $table->enum('status', ['draft', 'published', 'archived', 'under review'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->text('prerequisites')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
