<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The date an admin wants printed on this student's certificate, set on the
 * enrollment beside the grade. Used when the certificate is issued, and kept
 * in step with an already issued one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('certificate_date')->nullable()->after('completion_date');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('certificate_date');
        });
    }
};
