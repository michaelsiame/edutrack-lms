<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lenco_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('lenco_transactions', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('lenco_transactions', 'phone_number')) {
                $table->string('phone_number', 20)->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lenco_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'phone_number']);
        });
    }
};
