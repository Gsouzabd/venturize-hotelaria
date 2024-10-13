<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrecoEstoqueToProdutosTable extends Migration
{
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->decimal('preco_custo', 8, 2)->nullable()->after('valor_unitario');
            $table->decimal('preco_venda', 8, 2)->nullable()->after('preco_custo');
            $table->integer('estoque_minimo')->nullable()->after('preco_venda');
            $table->integer('estoque_maximo')->nullable()->after('estoque_minimo');
        });
    }

    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn('preco_custo');
            $table->dropColumn('preco_venda');
            $table->dropColumn('estoque_minimo');
            $table->dropColumn('estoque_maximo');
        });
    }
}