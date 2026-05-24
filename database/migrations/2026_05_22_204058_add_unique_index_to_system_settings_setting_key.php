<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The system_settings table was imported from the legacy PHP site
        // and uses legacy column names (setting_id, setting_key, etc.).
        // Add a unique index to prevent duplicate keys.
        Schema::table('system_settings', function (Blueprint $table) {
            $table->unique('setting_key', 'system_settings_setting_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropUnique('system_settings_setting_key_unique');
        });
    }
};
