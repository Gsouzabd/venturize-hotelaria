<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL only; no SQLite (testes) o enum é texto e aceita o novo valor
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE impressoes_pedidos MODIFY COLUMN status_impressao ENUM('pendente','processando','sucesso','erro') NOT NULL DEFAULT 'pendente'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE impressoes_pedidos MODIFY COLUMN status_impressao ENUM('pendente','sucesso','erro') NOT NULL DEFAULT 'pendente'");
        }
    }
};
