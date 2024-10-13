<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimentacaoEstoquesTable extends Migration
{
    public function up()
    {
        Schema::create('movimentacoes_estoque', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->foreignId('local_estoque_origem_id')->nullable()->constrained('locais_estoque')->onDelete('cascade');
            $table->foreignId('local_estoque_destino_id')->nullable()->constrained('locais_estoque')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');  // Relacionamento com o usuário
            $table->integer('quantidade');
            $table->enum('tipo', ['entrada', 'saida', 'transferencia']);
            $table->timestamp('data_movimentacao');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimentacoes_estoque');
    }
}