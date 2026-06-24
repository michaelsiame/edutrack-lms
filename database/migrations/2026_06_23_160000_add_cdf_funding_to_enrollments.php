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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('funding_source', ['self', 'cdf', 'bursary', 'employer'])->default('self')->after('mode');
            $table->string('cdf_constituency')->nullable()->after('funding_source');
            $table->string('sponsor_reference')->nullable()->after('cdf_constituency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['funding_source', 'cdf_constituency', 'sponsor_reference']);
        });
    }
};
