<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('registration_fees', 'mobile_provider')) {
                $table->string('mobile_provider', 20)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('registration_fees', 'mobile_reference')) {
                $table->string('mobile_reference', 100)->nullable()->after('mobile_provider');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registration_fees', function (Blueprint $table) {
            $table->dropColumn(['mobile_provider', 'mobile_reference']);
        });
    }
};
