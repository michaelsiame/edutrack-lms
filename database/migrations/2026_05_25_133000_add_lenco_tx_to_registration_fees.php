<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('registration_fees', 'lenco_transaction_id')) {
                $table->string('lenco_transaction_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('registration_fees', 'reference')) {
                $table->string('reference')->nullable()->after('lenco_transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            $table->dropColumn(['lenco_transaction_id', 'reference']);
        });
    }
};
