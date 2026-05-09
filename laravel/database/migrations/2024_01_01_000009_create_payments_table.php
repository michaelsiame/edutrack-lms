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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('student_id')->constrained('users');
            $table->foreignId('course_id')->constrained('courses');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments');
            $table->foreignId('payment_plan_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->foreignId('payment_method_id')->nullable();
            $table->enum('payment_type', ['registration', 'course_fee', 'partial_payment'])->default('course_fee');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->comment('User ID of admin/finance who recorded cash payment');
            $table->enum('payment_status', ['Pending', 'Completed', 'Failed', 'Refunded', 'Cancelled'])->default('Pending');
            $table->string('transaction_id', 100)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
