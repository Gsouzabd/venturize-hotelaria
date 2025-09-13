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
        Schema::create('impressoes_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('agente_impressao')->default('sistema'); // sistema, agente_externo, manual
            $table->string('ip_origem')->nullable();
            $table->enum('status_impressao', ['pendente', 'processando', 'sucesso', 'erro'])->default('pendente');
            $table->text('detalhes_erro')->nullable();
            $table->json('dados_impressao')->nullable(); // dados extras como impressora usada, etc.
            $table->timestamps();
            
            // Índices para performance
            $table->index(['pedido_id', 'status_impressao']);
            $table->index('created_at');
            $table->index('status_impressao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impressoes_pedidos');
    }
};