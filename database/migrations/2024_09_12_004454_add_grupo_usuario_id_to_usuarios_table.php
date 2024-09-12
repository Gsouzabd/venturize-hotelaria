<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGrupoUsuarioIdToUsuariosTable extends Migration
{
    public function up()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Verifica se a coluna já existe antes de tentar adicioná-la
            if (!Schema::hasColumn('usuarios', 'grupo_usuario_id')) {
                $table->foreignId('grupo_usuario_id')->nullable()->constrained('grupo_usuarios')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropForeign(['grupo_usuario_id']);
            $table->dropColumn('grupo_usuario_id');
        });
    }
}
