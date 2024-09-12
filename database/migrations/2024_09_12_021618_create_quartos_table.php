<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuartosTable extends Migration
{
    public function up()
    {
        Schema::create('quartos', function (Blueprint $table) {
            $table->id();
            $table->string('andar');
            $table->integer('numero');
            $table->string('ramal')->nullable();
            $table->enum('posicao_quarto', ['Frente', 'Fundos', 'Lateral']);
            $table->integer('quantidade_cama_casal')->default(0);
            $table->integer('quantidade_cama_solteiro')->default(0);
            $table->string('classificacao');
            $table->enum('acessibilidade', ['Sim', 'Não']);
            $table->enum('inativo', ['Sim', 'Não']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quartos');
    }
}
