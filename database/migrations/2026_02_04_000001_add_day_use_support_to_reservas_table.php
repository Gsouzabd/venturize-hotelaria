<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Day Use não ocupa quarto, então o quarto_id precisa ser opcional
            $table->foreignId('quarto_id')->nullable()->change();

            // Campos específicos de Day Use
            $table->boolean('com_cafe')->default(false)->after('criancas_mais_7');
            $table->decimal('valor_cafe', 8, 2)->nullable()->after('com_cafe');
        });

        // Atualizar enum tipo_reserva para incluir DAY_USE
        DB::statement("
            ALTER TABLE reservas
            MODIFY COLUMN tipo_reserva ENUM('INDIVIDUAL', 'GRUPO', 'DAY_USE') NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Reverter campos de Day Use
            $table->dropColumn('com_cafe');
            $table->dropColumn('valor_cafe');

            // Voltar a exigir quarto_id (mantendo o tipo original)
            $table->foreignId('quarto_id')->nullable(false)->change();
        });

        // Reverter enum tipo_reserva ao estado original
        DB::statement("
            ALTER TABLE reservas
            MODIFY COLUMN tipo_reserva ENUM('INDIVIDUAL', 'GRUPO') NULL
        ");
    }
};

