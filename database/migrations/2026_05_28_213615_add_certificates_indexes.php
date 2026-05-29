<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->index('verification_code', 'certificates_verification_code_index');
            $table->index(['user_id', 'course_id'], 'certificates_user_course_index');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex('certificates_verification_code_index');
            $table->dropIndex('certificates_user_course_index');
        });
    }
};
