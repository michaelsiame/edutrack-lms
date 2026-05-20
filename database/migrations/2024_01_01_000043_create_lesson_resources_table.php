<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('resource_type', ['PDF', 'Document', 'Spreadsheet', 'Presentation', 'Video', 'Audio', 'Archive', 'Other']);
            $table->string('file_url', 255);
            $table->integer('file_size_kb')->nullable();
            $table->integer('download_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_resources');
    }
};
