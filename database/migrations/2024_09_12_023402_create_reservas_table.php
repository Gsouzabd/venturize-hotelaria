<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_reserva', ['INDIVIDUAL', 'GRUPO'])->nullable();
            $table->enum('situacao_reserva', ['PRÉ RESERVA', 'RESERVADO', 'CANCELADA', 'HOSPEDADO', 'NO SHOW', 'FINALIZADO'])->default('PRÉ RESERVA');
            $table->time('previsao_chegada')->nullable();
            $table->time('previsao_saida')->nullable();

            // Relacionamento com Cliente
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Relacionamento com Quarto
            $table->foreignId('quarto_id')->constrained('quartos')->onDelete('cascade');

            // Relacionamento com Usuário Operador
            $table->bigInteger('usuario_operador_id');

            $table->string('email_solicitante')->nullable();
            $table->string('celular')->nullable();
            $table->string('email_faturamento')->nullable();

            // Relacionamento com Empresa Faturamento (Cliente)
            $table->foreignId('empresa_faturamento_id')->nullable()->constrained('clientes')->onDelete('set null');

            // Relacionamento com Empresa Solicitante (Cliente)
            $table->foreignId('empresa_solicitante_id')->nullable()->constrained('clientes')->onDelete('set null');

            $table->text('observacoes')->nullable();
            $table->text('observacoes_internas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
