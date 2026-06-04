<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedBigInteger('woocommerce_order_id')->nullable()->after('cart_serialized');
            $table->index('woocommerce_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['woocommerce_order_id']);
            $table->dropColumn('woocommerce_order_id');
        });
    }
};
