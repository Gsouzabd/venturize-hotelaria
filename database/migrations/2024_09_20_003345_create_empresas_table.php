<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social');       // Razão Social
            $table->string('nome_fantasia');      // Nome Fantasia
            $table->string('cnpj')->unique();     // CNPJ
            $table->string('inscricao_estadual')->nullable(); // IE (Inscrição Estadual)
            $table->string('email')->nullable();  // Email
            $table->string('telefone')->nullable(); // Telefone
            $table->timestamps();  // Data de criação e atualização
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresas');
    }
}
