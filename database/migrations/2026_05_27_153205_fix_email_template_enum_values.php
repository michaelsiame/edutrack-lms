<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_templates')) {
            DB::statement("ALTER TABLE email_templates MODIFY COLUMN template_type VARCHAR(50) DEFAULT 'Custom'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('email_templates')) {
            DB::statement("ALTER TABLE email_templates MODIFY COLUMN template_type ENUM('Welcome', 'Enrollment', 'Certificate', 'Payment', 'Reminder', 'Custom') DEFAULT 'Custom'");
        }
    }
};
