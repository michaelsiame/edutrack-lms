<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_versions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lesson_id');
            $table->longText('content')->nullable();
            $table->unsignedInteger('version_number');
            $table->string('change_summary', 255)->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['lesson_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_versions');
    }
};
