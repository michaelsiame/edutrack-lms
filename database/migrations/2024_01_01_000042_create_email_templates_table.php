<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->string('template_name', 100);
            $table->string('subject', 200);
            $table->longText('body');
            $table->enum('template_type', ['Welcome', 'Enrollment', 'Certificate', 'Payment', 'Reminder', 'Custom'])->default('Custom');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
