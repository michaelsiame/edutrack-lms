<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id('badge_id');
            $table->string('badge_name', 100);
            $table->text('description')->nullable();
            $table->string('badge_icon_url', 255)->nullable();
            $table->enum('badge_type', ['Course Completion', 'Perfect Score', 'Early Bird', 'Participation', 'Streak', 'Custom'])->default('Custom');
            $table->text('criteria')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
