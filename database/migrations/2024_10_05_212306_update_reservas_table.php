<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateReservasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->integer('adultos')->default(0); // Adiciona a coluna adultos
            $table->integer('criancas_ate_7')->default(0); // Adiciona a coluna criancas_ate_7
            $table->integer('criancas_mais_7')->default(0); // Adiciona a coluna criancas_mais_7
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('adultos'); // Remove a coluna adultos
            $table->dropColumn('criancas_ate_7'); // Remove a coluna criancas_ate_7
            $table->dropColumn('criancas_mais_7'); // Remove a coluna criancas_mais_7
        });
    }
}