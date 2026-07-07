<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Schema builder não altera enum no MySQL — usar SQL direto
            DB::statement("ALTER TABLE movimentacoes_estoque MODIFY tipo ENUM('entrada', 'saida', 'transferencia', 'perda', 'estorno') NOT NULL");

            // Produtos em KG/LT precisam de quantidade fracionada (integer truncava)
            DB::statement('ALTER TABLE movimentacoes_estoque MODIFY quantidade DECIMAL(10,2) NOT NULL');
            DB::statement('ALTER TABLE estoques MODIFY quantidade DECIMAL(10,2) NOT NULL');

            return;
        }

        // SQLite (testes): sem MODIFY; o schema builder recria as colunas
        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->decimal('quantidade', 10, 2)->change();
            $table->string('tipo')->change();
        });
        Schema::table('estoques', function (Blueprint $table) {
            $table->decimal('quantidade', 10, 2)->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE movimentacoes_estoque MODIFY tipo ENUM('entrada', 'saida', 'transferencia') NOT NULL");
            DB::statement('ALTER TABLE movimentacoes_estoque MODIFY quantidade INT NOT NULL');
            DB::statement('ALTER TABLE estoques MODIFY quantidade INT NOT NULL');

            return;
        }

        Schema::table('movimentacoes_estoque', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });
        Schema::table('estoques', function (Blueprint $table) {
            $table->integer('quantidade')->change();
        });
    }
};
