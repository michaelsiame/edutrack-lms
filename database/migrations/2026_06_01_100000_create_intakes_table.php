<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intakes', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('application_deadline')->nullable();
            $table->date('learning_deadline')->nullable();
            $table->unsignedInteger('max_students')->default(0);
            $table->unsignedInteger('enrollment_count')->default(0);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'in_progress', 'completed'])->default('draft');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intakes');
    }
};
