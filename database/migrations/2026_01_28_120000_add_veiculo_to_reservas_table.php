<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('veiculo_modelo')->nullable()->after('observacoes_internas');
            $table->string('veiculo_cor')->nullable()->after('veiculo_modelo');
            $table->string('veiculo_placa')->nullable()->after('veiculo_cor');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['veiculo_modelo', 'veiculo_cor', 'veiculo_placa']);
        });
    }
};
