<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('day_use_plano_precos', function (Blueprint $table) {
            $table->decimal('preco_cafe_semana', 8, 2)->nullable()->after('preco_cafe');
            $table->decimal('preco_cafe_fim_semana', 8, 2)->nullable()->after('preco_cafe_semana');
        });

        // Copiar o valor atual de preco_cafe para os novos campos, para não quebrar planos existentes
        DB::table('day_use_plano_precos')
            ->whereNotNull('preco_cafe')
            ->update([
                'preco_cafe_semana' => DB::raw('COALESCE(preco_cafe_semana, preco_cafe)'),
                'preco_cafe_fim_semana' => DB::raw('COALESCE(preco_cafe_fim_semana, preco_cafe)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_use_plano_precos', function (Blueprint $table) {
            $table->dropColumn('preco_cafe_semana');
            $table->dropColumn('preco_cafe_fim_semana');
        });
    }
};

