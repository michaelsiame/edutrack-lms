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
        // MySQL requires altering the entire enum to change one value
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived', 'under_review'])->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published', 'archived', 'under review'])->default('draft')->change();
        });
    }
};
