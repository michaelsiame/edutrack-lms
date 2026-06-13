<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Historic enrollments were left with certificate_blocked=1 even after
 * payment completed, because the old payment sync never cleared the flag
 * (fixed in LencoPaymentService::updateEnrollmentPaymentStatus). Clear the
 * stale flags for fully-paid enrollments.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('enrollments')
            ->where('payment_status', 'completed')
            ->where('certificate_blocked', 1)
            ->update(['certificate_blocked' => 0]);
    }

    public function down(): void
    {
        // Data fix; nothing to restore.
    }
};
