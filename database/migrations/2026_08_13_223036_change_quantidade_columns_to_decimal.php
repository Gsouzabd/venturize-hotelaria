<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'estoques.quantidade' e 'movimentacoes_estoque.quantidade' eram INTEGER, truncando
     * silenciosamente frações de produtos com unidade fracionária (KG, LT). Convertidas
     * para DECIMAL(12,3). Implementado via add + copy + drop + rename (em vez de
     * Schema::change()) para não depender de doctrine/dbal, que não está instalado.
     */
    public function up(): void
    {
        $this->converterParaDecimal('estoques', 0);
        $this->converterParaDecimal('movimentacoes_estoque', null);
    }

    public function down(): void
    {
        $this->converterParaInteger('estoques', 0);
        $this->converterParaInteger('movimentacoes_estoque', null);
    }

    private function converterParaDecimal(string $tabela, ?int $default): void
    {
        Schema::table($tabela, function (Blueprint $table) {
            $table->decimal('quantidade_decimal_tmp', 12, 3)->nullable()->after('quantidade');
        });

        DB::statement("UPDATE {$tabela} SET quantidade_decimal_tmp = quantidade");

        Schema::table($tabela, function (Blueprint $table) {
            $table->dropColumn('quantidade');
        });

        Schema::table($tabela, function (Blueprint $table) {
            $table->renameColumn('quantidade_decimal_tmp', 'quantidade');
        });

        if ($default !== null) {
            DB::statement("UPDATE {$tabela} SET quantidade = {$default} WHERE quantidade IS NULL");
        }

        // Torna a coluna NOT NULL (driver-specific; SQLite não suporta ALTER COLUMN,
        // mas a coluna recém criada via decimal()->nullable() já atende os testes).
        if (DB::getDriverName() === 'mysql') {
            $notNull = $default !== null ? "DEFAULT {$default}.000 NOT NULL" : 'NOT NULL';
            DB::statement("ALTER TABLE {$tabela} MODIFY quantidade DECIMAL(12,3) {$notNull}");
        }
    }

    private function converterParaInteger(string $tabela, ?int $default): void
    {
        Schema::table($tabela, function (Blueprint $table) {
            $table->integer('quantidade_int_tmp')->nullable()->after('quantidade');
        });

        $castType = DB::getDriverName() === 'mysql' ? 'SIGNED' : 'INTEGER';
        DB::statement("UPDATE {$tabela} SET quantidade_int_tmp = CAST(quantidade AS {$castType})");

        Schema::table($tabela, function (Blueprint $table) {
            $table->dropColumn('quantidade');
        });

        Schema::table($tabela, function (Blueprint $table) {
            $table->renameColumn('quantidade_int_tmp', 'quantidade');
        });

        if ($default !== null) {
            DB::statement("UPDATE {$tabela} SET quantidade = {$default} WHERE quantidade IS NULL");
        }
    }
};
