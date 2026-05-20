<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lenco_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('lenco_transaction_id', 100)->unique();
            $table->foreignId('payment_id')->nullable()->constrained('payments', 'payment_id');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('ZMW');
            $table->string('status', 50)->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('lenco_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lenco_transactions');
    }
};
