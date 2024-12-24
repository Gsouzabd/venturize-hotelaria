<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddObservacoesToPagamentosTable extends Migration
{
    public function up()
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->text('observacoes')->nullable()->after('valores_recebidos');
        });
    }

    public function down()
    {
        Schema::table('pagamentos', function (Blueprint $table) {
            $table->dropColumn('observacoes');
        });
    }
}