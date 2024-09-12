<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoUsuariosTable extends Migration
{
    public function up()
    {
        Schema::create('grupo_usuarios', function (Blueprint $table) {
            $table->id(); // Isso cria um bigint como chave primária
            $table->string('nome'); // Nome do grupo de usuário
            $table->timestamps(); // Campos padrão de created_at e updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupo_usuarios');
    }
}
