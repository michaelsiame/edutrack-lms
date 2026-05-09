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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('google_id', 255)->nullable();
            $table->string('password_hash', 255);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('phone', 20)->nullable();
            $table->string('avatar_url', 255)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('active');
            $table->string('email_verification_token', 255)->nullable();
            $table->timestamp('email_verification_expires')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('account_locked_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
