<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id(); // Código
            $table->string('descricao'); // Descrição
            $table->decimal('valor_unitario', 8, 2); // Valor Unitário
            $table->string('categoria_produto'); // Categoria do Produto
            $table->string('codigo_barras_produto')->nullable(); // Cód. Barras Produto (opcional)
            $table->string('codigo_interno')->nullable(); // Código Interno
            $table->string('impressora')->nullable(); // Impressora (opcional)
            $table->string('unidade'); // Unidade
            $table->boolean('ativo')->default(true); // Ativo?
            $table->string('criado_por'); // Criado por
            $table->string('complemento')->nullable(); // Complemento (opcional)
            $table->string('produto_servico'); // Produto/Serviço
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
