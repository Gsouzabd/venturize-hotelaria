<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValorUnitarioToMovimentacoesEstoqueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->decimal('valor_unitario_custo', 10, 2)->nullable()->after('quantidade');
            $table->decimal('valor_unitario_venda', 10, 2)->nullable()->after('valor_unitario_custo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->dropColumn('valor_unitario_custo');
            $table->dropColumn('valor_unitario_venda');
        });
    }
}