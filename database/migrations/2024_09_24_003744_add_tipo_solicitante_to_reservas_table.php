<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoSolicitanteToReservasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('tipo_solicitante')->nullable()->after('tipo_reserva'); // Adiciona a coluna 'tipo_solicitante' após a coluna 'tipo_reserva'
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
            $table->dropColumn('tipo_solicitante'); // Remove a coluna 'tipo_solicitante'
        });
    }
}