<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxaServicoAndTotalComTaxaToPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('taxa_servico', 8, 2)->nullable()->after('total');
            $table->decimal('total_com_taxa', 8, 2)->nullable()->after('taxa_servico');
            $table->boolean('remover_taxa')->default(false)->after('taxa_servico');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('taxa_servico');
            $table->dropColumn('total_com_taxa');
        });
    }
}