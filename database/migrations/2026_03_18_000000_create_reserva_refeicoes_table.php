<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_refeicoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained()->onDelete('cascade');
            $table->string('hospede_nome');
            $table->string('hospede_tipo')->default('titular'); // titular/acompanhante
            $table->unsignedBigInteger('acompanhante_id')->nullable();
            $table->boolean('cafe')->default(false);
            $table->boolean('almoco')->default(false);
            $table->boolean('jantar')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_refeicoes');
    }
};
