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
        // Fix lenco_transactions table - add missing columns used by the application
        Schema::table('lenco_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('lenco_transactions', 'reference')) {
                $table->string('reference', 100)->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('lenco_transactions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('payment_id')->constrained('users');
            }
            if (!Schema::hasColumn('lenco_transactions', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('user_id')->constrained('courses');
            }
            if (!Schema::hasColumn('lenco_transactions', 'virtual_account_number')) {
                $table->string('virtual_account_number', 50)->nullable()->after('currency');
            }
            if (!Schema::hasColumn('lenco_transactions', 'virtual_account_bank')) {
                $table->string('virtual_account_bank', 100)->nullable()->after('virtual_account_number');
            }
            if (!Schema::hasColumn('lenco_transactions', 'virtual_account_name')) {
                $table->string('virtual_account_name', 150)->nullable()->after('virtual_account_bank');
            }
            if (!Schema::hasColumn('lenco_transactions', 'lenco_account_id')) {
                $table->string('lenco_account_id', 100)->nullable()->after('virtual_account_name');
            }
            if (!Schema::hasColumn('lenco_transactions', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('lenco_transactions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('lenco_transactions', 'metadata')) {
                $table->json('metadata')->nullable()->after('expires_at');
            }
        });

        // Fix lenco_webhook_logs table - add missing signature columns
        Schema::table('lenco_webhook_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('lenco_webhook_logs', 'signature')) {
                $table->string('signature', 255)->nullable()->after('payload');
            }
            if (!Schema::hasColumn('lenco_webhook_logs', 'signature_valid')) {
                $table->boolean('signature_valid')->nullable()->after('signature');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lenco_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'reference',
                'user_id',
                'course_id',
                'virtual_account_number',
                'virtual_account_bank',
                'virtual_account_name',
                'lenco_account_id',
                'paid_at',
                'expires_at',
                'metadata',
            ]);
        });

        Schema::table('lenco_webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signature_valid']);
        });
    }
};
