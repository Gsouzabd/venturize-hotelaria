<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropColumn('metodo_pagamento');  // Remove a coluna metodo_pagamento
            $table->text('valores_recebidos')->nullable();  // Adiciona a coluna valores_recebidos
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->enum('metodo_pagamento', ['PIX', 'DINHEIRO', 'CARTAO_CREDITO', 'TRANSFERENCIA']);  // Adiciona a coluna metodo_pagamento de volta
            $table->dropColumn('valores_recebidos');  // Remove a coluna valores_recebidos
        });
    }
}