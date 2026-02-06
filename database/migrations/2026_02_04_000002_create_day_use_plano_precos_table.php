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
        Schema::create('day_use_plano_precos', function (Blueprint $table) {
            $table->id();

            // Período de vigência (opcional)
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();

            // Indica se é o plano padrão
            $table->boolean('is_default')->default(false);

            // Preços por dia da semana
            $table->decimal('preco_segunda', 8, 2)->nullable();
            $table->decimal('preco_terca', 8, 2)->nullable();
            $table->decimal('preco_quarta', 8, 2)->nullable();
            $table->decimal('preco_quinta', 8, 2)->nullable();
            $table->decimal('preco_sexta', 8, 2)->nullable();
            $table->decimal('preco_sabado', 8, 2)->nullable();
            $table->decimal('preco_domingo', 8, 2)->nullable();

            // Valor adicional do café da manhã
            $table->decimal('preco_cafe', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_use_plano_precos');
    }
};

