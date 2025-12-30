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
            $table->foreignId('fornecedor_id')->nullable()->after('usuario_id')->constrained('fornecedores')->onDelete('set null');
            $table->index('fornecedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despesas', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropIndex(['fornecedor_id']);
            $table->dropColumn('fornecedor_id');
        });
    }
};

