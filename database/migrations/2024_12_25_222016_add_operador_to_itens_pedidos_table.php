<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperadorToItensPedidosTable extends Migration
{
    public function up()
    {
        Schema::table('itens_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('operador_id')->nullable()->after('preco');
            $table->foreign('operador_id')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('itens_pedidos', function (Blueprint $table) {
            $table->dropForeign(['operador_id']);
            $table->dropColumn('operador_id');
        });
    }
}