<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the foreign key constraint first
        Schema::table('payments', function ($table) {
            $table->dropForeign('fk_pay_course');
        });

        // Make course_id nullable
        DB::statement('ALTER TABLE payments MODIFY course_id INT NULL');

        // Re-add the foreign key
        Schema::table('payments', function ($table) {
            $table->foreign('course_id', 'fk_pay_course')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function ($table) {
            $table->dropForeign('fk_pay_course');
        });

        DB::statement('ALTER TABLE payments MODIFY course_id INT NOT NULL');

        Schema::table('payments', function ($table) {
            $table->foreign('course_id', 'fk_pay_course')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
        });
    }
};
