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
        Schema::table('despesas', function (Blueprint $table) {
            $table->string('numero_nota_fiscal')->nullable()->change();
            $table->foreignId('fornecedor_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->string('numero_nota_fiscal')->nullable(false)->change();
            $table->foreignId('fornecedor_id')->nullable()->change();
        });
    }
};
