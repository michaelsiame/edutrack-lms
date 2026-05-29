<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lenco_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('lenco_transactions', 'payment_id')) {
                $table->unsignedBigInteger('payment_id')->nullable()->after('id');
            }
        });

        Schema::table('lenco_webhook_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('lenco_webhook_logs', 'lenco_transaction_id')) {
                $table->string('lenco_transaction_id')->nullable()->after('event_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lenco_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_id');
        });
        Schema::table('lenco_webhook_logs', function (Blueprint $table) {
            $table->dropColumn('lenco_transaction_id');
        });
    }
};
