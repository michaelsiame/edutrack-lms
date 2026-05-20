<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lenco_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->string('lenco_transaction_id', 100)->nullable();
            $table->text('payload');
            $table->string('ip_address', 45)->nullable();
            $table->boolean('processed')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lenco_webhook_logs');
    }
};
