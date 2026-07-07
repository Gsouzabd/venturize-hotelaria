<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Schema builder não altera enum — usar SQL direto
        DB::statement("ALTER TABLE movimentacoes_estoque MODIFY tipo ENUM('entrada', 'saida', 'transferencia', 'perda', 'estorno') NOT NULL");

        // Produtos em KG/LT precisam de quantidade fracionada (integer truncava)
        DB::statement('ALTER TABLE movimentacoes_estoque MODIFY quantidade DECIMAL(10,2) NOT NULL');
        DB::statement('ALTER TABLE estoques MODIFY quantidade DECIMAL(10,2) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE movimentacoes_estoque MODIFY tipo ENUM('entrada', 'saida', 'transferencia') NOT NULL");
        DB::statement('ALTER TABLE movimentacoes_estoque MODIFY quantidade INT NOT NULL');
        DB::statement('ALTER TABLE estoques MODIFY quantidade INT NOT NULL');
    }
};
