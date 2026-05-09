<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id('reply_id');
            $table->foreignId('discussion_id')->constrained('discussions', 'discussion_id')->onDelete('cascade');
            $table->foreignId('parent_reply_id')->nullable()->constrained('discussion_replies', 'reply_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_instructor_reply')->default(false);
            $table->boolean('is_best_answer')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_replies');
    }
};
