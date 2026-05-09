<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->decimal('total_fee', 10, 2)->comment('Full course fee');
            $table->decimal('total_paid', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->storedAs('total_fee - total_paid');
            $table->string('currency', 3)->default('ZMW');
            $table->enum('payment_status', ['pending', 'partial', 'completed', 'overdue'])->default('pending');
            $table->date('due_date')->nullable()->comment('Final payment due date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_payment_plans');
    }
};
