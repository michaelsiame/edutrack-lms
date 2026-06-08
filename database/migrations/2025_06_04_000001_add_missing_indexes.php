<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enrollment lookups (most critical)
        if (!Schema::hasIndex('enrollments', 'enrollments_user_course_idx')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->index(['user_id', 'course_id'], 'enrollments_user_course_idx');
                $table->index(['enrollment_status', 'payment_status'], 'enrollments_status_idx');
            });
        }

        // Payment lookups
        if (!Schema::hasIndex('payments', 'payments_enrollment_status_idx')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['enrollment_id', 'payment_status'], 'payments_enrollment_status_idx');
                $table->index('transaction_id', 'payments_transaction_idx');
            });
        }

        // Certificate lookups
        if (!Schema::hasIndex('certificates', 'certificates_enrollment_idx')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->index('enrollment_id', 'certificates_enrollment_idx');
                $table->index('certificate_number', 'certificates_number_idx');
                $table->index('verification_code', 'certificates_verify_idx');
            });
        }

        // Lenco transaction lookups
        if (!Schema::hasIndex('lenco_transactions', 'lenco_tx_reference_idx')) {
            Schema::table('lenco_transactions', function (Blueprint $table) {
                $table->index('reference', 'lenco_tx_reference_idx');
                $table->index(['enrollment_id', 'status'], 'lenco_tx_enrollment_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_user_course_idx');
            $table->dropIndex('enrollments_status_idx');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_enrollment_status_idx');
            $table->dropIndex('payments_transaction_idx');
        });
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex('certificates_enrollment_idx');
            $table->dropIndex('certificates_number_idx');
            $table->dropIndex('certificates_verify_idx');
        });
        Schema::table('lenco_transactions', function (Blueprint $table) {
            $table->dropIndex('lenco_tx_reference_idx');
            $table->dropIndex('lenco_tx_enrollment_status_idx');
        });
    }
};
