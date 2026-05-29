<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Add columns that the model/controller expects
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('events', 'excerpt')) {
                $table->string('excerpt', 500)->nullable()->after('description');
            }
            if (!Schema::hasColumn('events', 'category')) {
                $table->string('category', 50)->nullable()->after('excerpt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['description', 'excerpt', 'category']);
        });
    }
};
