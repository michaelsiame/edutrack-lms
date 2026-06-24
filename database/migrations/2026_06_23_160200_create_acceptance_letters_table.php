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
        Schema::create('acceptance_letters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('enrollment_id');
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->cascadeOnDelete();
            $table->string('reference_no')->unique();
            $table->string('student_name');
            $table->string('course_title');
            $table->string('mode');
            $table->string('duration');
            $table->date('commencement_date')->nullable();
            $table->json('fee_snapshot');
            $table->date('issued_date');
            $table->string('signed_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_letters');
    }
};
