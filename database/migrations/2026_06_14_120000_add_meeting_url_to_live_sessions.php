<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            // Optional external meeting link (Google Meet, self-hosted Jitsi, etc.).
            // When set, students are sent here; otherwise the built-in Jitsi room is used.
            $table->string('meeting_url', 500)->nullable()->after('meeting_room_id');
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn('meeting_url');
        });
    }
};
