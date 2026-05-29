<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the duplicate job_title I accidentally added; use existing current_job_title
        if (Schema::hasColumn('testimonials', 'job_title')) {
            Schema::table('testimonials', function (Blueprint $table) {
                $table->dropColumn('job_title');
            });
        }
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('job_title')->nullable()->after('testimonial_text');
        });
    }
};
