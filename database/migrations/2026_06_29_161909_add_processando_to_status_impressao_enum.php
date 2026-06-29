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
        DB::statement("ALTER TABLE impressoes_pedidos MODIFY COLUMN status_impressao ENUM('pendente','processando','sucesso','erro') NOT NULL DEFAULT 'pendente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE impressoes_pedidos MODIFY COLUMN status_impressao ENUM('pendente','sucesso','erro') NOT NULL DEFAULT 'pendente'");
    }
};
