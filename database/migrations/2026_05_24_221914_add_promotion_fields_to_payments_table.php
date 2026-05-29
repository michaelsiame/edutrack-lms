<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id')->nullable()->after('payment_status');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('promotion_id');

            $table->foreign('promotion_id')
                  ->references('id')
                  ->on('promotions')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'discount_amount']);
        });
    }
};
