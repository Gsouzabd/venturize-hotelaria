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
        Schema::create('despesa_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despesa_id')->constrained('despesas')->onDelete('cascade');
            $table->foreignId('categoria_despesa_id')->nullable()->constrained('categorias_despesas')->onDelete('set null');
            $table->decimal('valor', 10, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index('despesa_id');
            $table->index('categoria_despesa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesa_categorias');
    }
};
