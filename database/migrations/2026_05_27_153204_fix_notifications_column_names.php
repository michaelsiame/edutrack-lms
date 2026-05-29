<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('notifications', 'type') && !Schema::hasColumn('notifications', 'notification_type')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->renameColumn('type', 'notification_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notifications', 'notification_type') && !Schema::hasColumn('notifications', 'type')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->renameColumn('notification_type', 'type');
            });
        }
    }
};
