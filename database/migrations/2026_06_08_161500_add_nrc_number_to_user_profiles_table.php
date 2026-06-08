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
        if (!Schema::hasColumn('user_profiles', 'nrc_number')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->string('nrc_number', 20)->nullable()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_profiles', 'nrc_number')) {
            Schema::table('user_profiles', function (Blueprint $table) {
                $table->dropColumn('nrc_number');
            });
        }
    }
};
