<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'job_title')) {
                $table->string('job_title')->nullable()->after('testimonial_text');
            }
            if (!Schema::hasColumn('testimonials', 'company')) {
                $table->string('company')->nullable()->after('job_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (Schema::hasColumn('testimonials', 'job_title')) {
                $table->dropColumn('job_title');
            }
            if (Schema::hasColumn('testimonials', 'company')) {
                $table->dropColumn('company');
            }
        });
    }
};
