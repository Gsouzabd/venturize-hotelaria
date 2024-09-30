<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();  // ID do pagamento
            $table->foreignId('reserva_id')->constrained('reservas')->onDelete('cascade');  // FK para reservas
            $table->enum('metodo_pagamento', ['PIX', 'DINHEIRO', 'CARTAO_CREDITO', 'TRANSFERENCIA']);
            $table->decimal('valor_pago', 10, 2);  // Valor já pago
            $table->decimal('valor_total', 10, 2);  // Valor total da reserva
            $table->decimal('valor_sinal', 10, 2)->nullable();  // Se for um pagamento parcial
            $table->enum('status_pagamento', ['PAGO', 'PARCIAL', 'PENDENTE']);
            $table->timestamp('data_pagamento')->nullable();  // Data de pagamento
            $table->timestamps();  // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pagamentos');
    }
}
