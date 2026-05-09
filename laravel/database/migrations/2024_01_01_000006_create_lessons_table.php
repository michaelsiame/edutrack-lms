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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->enum('lesson_type', ['Video', 'Reading', 'Quiz', 'Assignment', 'Live Session', 'Download'])->default('Reading');
            $table->integer('duration_minutes')->nullable();
            $table->integer('display_order')->default(0);
            $table->string('video_url', 255)->nullable();
            $table->integer('video_duration')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
