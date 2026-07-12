<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'partial' to the enrollments.payment_status enum so partially-paid
     * enrollments can be distinguished from pending/unpaid ones.
     */
    public function up(): void
    {
        if (Schema::hasTable('enrollments')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                DB::statement("ALTER TABLE enrollments MODIFY COLUMN payment_status ENUM('pending', 'partial', 'completed', 'failed', 'refunded') DEFAULT 'pending'");
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('enrollments')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            try {
                DB::statement("ALTER TABLE enrollments MODIFY COLUMN payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending'");
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }
};
