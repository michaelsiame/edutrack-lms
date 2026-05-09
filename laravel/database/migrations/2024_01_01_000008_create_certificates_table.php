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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id('certificate_id');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->string('certificate_number', 50)->unique();
            $table->date('issued_date');
            $table->string('verification_code', 100)->nullable();
            $table->decimal('final_score', 5, 2)->default(0.00);
            $table->timestamp('issued_at')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->date('expiry_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
