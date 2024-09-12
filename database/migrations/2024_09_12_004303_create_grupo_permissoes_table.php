<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoPermissoesTable extends Migration
{
    public function up()
    {
        Schema::create('grupo_permissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_usuario_id')->constrained('grupo_usuarios')->onDelete('cascade');
            $table->foreignId('permissao_id')->constrained('permissoes')->onDelete('cascade'); // Alterado de 'permissaos' para 'permissoes'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grupo_permissoes');
    }
}
