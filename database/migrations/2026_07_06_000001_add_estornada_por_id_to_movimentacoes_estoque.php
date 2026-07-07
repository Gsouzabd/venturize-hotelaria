<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            // Aponta para a movimentação de estorno que cancelou esta; null = não estornada
            $table->foreignId('estornada_por_id')->nullable()->after('justificativa')
                ->constrained('movimentacoes_estoque')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estornada_por_id');
        });
    }
};
