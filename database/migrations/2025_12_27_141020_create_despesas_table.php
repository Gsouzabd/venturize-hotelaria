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
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_nota_fiscal');
            $table->string('arquivo_nota')->nullable();
            $table->date('data');
            $table->decimal('valor_total', 10, 2);
            $table->text('observacoes')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamps();
            
            $table->index('data');
            $table->index('numero_nota_fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
