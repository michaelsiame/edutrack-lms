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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->date('enrolled_at');
            $table->date('start_date')->nullable();
            $table->decimal('progress', 5, 2)->default(0.00);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->enum('enrollment_status', ['Enrolled', 'In Progress', 'Completed', 'Dropped', 'Expired'])->default('Enrolled');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->date('completion_date')->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->boolean('certificate_blocked')->default(false)->comment('Certificate blocked until fully paid');
            $table->timestamp('last_accessed')->nullable();
            $table->integer('total_time_spent')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
