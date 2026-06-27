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
        Schema::create('cdf_disbursements', function (Blueprint $table) {
            $table->id();
            $table->string('constituency');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('ZMW');
            $table->date('received_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->integer('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdf_disbursements');
    }
};
