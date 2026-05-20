<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            $table->string('meeting_room_id', 255);
            $table->timestamp('scheduled_start_time')->nullable();
            $table->timestamp('scheduled_end_time')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->integer('max_participants')->nullable();
            $table->text('description')->nullable();
            $table->string('recording_url', 500)->nullable();
            $table->string('moderator_password', 100)->nullable();
            $table->string('participant_password', 100)->nullable();
            $table->boolean('allow_recording')->default(true);
            $table->boolean('auto_start_recording')->default(false);
            $table->boolean('enable_chat')->default(true);
            $table->boolean('enable_screen_share')->default(true);
            $table->integer('buffer_minutes_before')->default(15);
            $table->integer('buffer_minutes_after')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
