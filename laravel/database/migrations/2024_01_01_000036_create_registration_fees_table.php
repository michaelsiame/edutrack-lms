<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('set null');
            $table->decimal('amount', 10, 2)->default(150.00);
            $table->string('currency', 3)->default('ZMW');
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['bank_transfer', 'bank_deposit', 'mobile_money'])->default('bank_deposit');
            $table->string('bank_reference', 100)->nullable()->comment('Bank deposit slip or transfer reference');
            $table->string('bank_name', 100)->nullable();
            $table->date('deposit_date')->nullable();
            $table->string('phone_number', 20)->nullable()->comment('Mobile money phone number');
            $table->foreignId('verified_by')->nullable()->constrained('users')->comment('Admin/Finance user who verified the payment');
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_fees');
    }
};
