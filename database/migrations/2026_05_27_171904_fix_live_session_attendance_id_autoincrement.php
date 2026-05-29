<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $column = DB::selectOne("SHOW COLUMNS FROM live_session_attendance WHERE Field = 'id'");
        if ($column && stripos($column->Extra, 'auto_increment') === false) {
            DB::statement('ALTER TABLE live_session_attendance MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE live_session_attendance MODIFY id BIGINT UNSIGNED NOT NULL');
    }
};
