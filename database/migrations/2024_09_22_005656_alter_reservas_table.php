<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReservasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Renomear a coluna cliente_id para cliente_solicitante_id
            $table->renameColumn('cliente_id', 'cliente_solicitante_id');

            // Adicionar a nova coluna cliente_responsavel_id
            $table->unsignedBigInteger('cliente_responsavel_id')->nullable()->after('cliente_solicitante_id');

            // Se necessário, adicionar a chave estrangeira para cliente_responsavel_id
            $table->foreign('cliente_responsavel_id')->references('id')->on('clientes')->onDelete('set null');
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
            // Reverter as alterações
            $table->dropForeign(['cliente_responsavel_id']);
            $table->dropColumn('cliente_responsavel_id');
            $table->renameColumn('cliente_solicitante_id', 'cliente_id');
        });
    }
}