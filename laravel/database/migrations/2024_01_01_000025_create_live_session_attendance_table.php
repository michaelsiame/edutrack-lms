<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->enum('status', ['present', 'absent', 'late'])->default('absent');
            $table->timestamps();

            $table->unique(['live_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_attendance');
    }
};
