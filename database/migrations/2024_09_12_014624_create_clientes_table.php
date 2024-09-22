<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['Pessoa Física', 'Pessoa Jurídica'])->default('Pessoa Física');
            $table->enum('estrangeiro', ['Sim', 'Não'])->default('Não');
            $table->enum('sexo', ['M', 'F'])->nullable();
            $table->string('nome');
            $table->date('data_nascimento')->nullable();
            $table->string('cpf')->nullable()->unique();
            $table->string('rg')->nullable();
            $table->string('passaporte')->nullable();
            $table->string('orgao_expedidor')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('inscricao_estadual_pf')->nullable();
            $table->string('cep')->nullable();
            $table->string('cidade')->nullable();
            $table->string('endereco')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('pais')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('email_alternativo')->nullable();
            $table->string('telefone')->nullable();
            $table->string('celular')->nullable();
            $table->string('profissao')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
