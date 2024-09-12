<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCheckinCheckoutToReservasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Definindo valor padrão como a data e hora atual para evitar erro
            $table->dateTime('data_checkin')->default(DB::raw('CURRENT_TIMESTAMP'))->after('previsao_saida');
            $table->dateTime('data_checkout')->default(DB::raw('CURRENT_TIMESTAMP'))->after('data_checkin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('data_checkin');
            $table->dropColumn('data_checkout');
        });
    }
}
