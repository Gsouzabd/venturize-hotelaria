<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id(); // ID do usuário
            $table->string('nome'); // Nome do usuário
            $table->string('email')->unique(); // Email do usuário (único)
            $table->string('senha'); // Senha do usuário
            $table->string('tipo')->default('usuario'); // Tipo de usuário (ex: administrador, usuário)
            $table->boolean('fl_ativo')->default(true); // Usuário ativo ou não
            $table->foreignId('grupo_usuario_id')->nullable()->constrained('grupo_usuarios')->onDelete('set null'); // Relacionamento com o grupo de usuários
            $table->timestamps(); // Campos padrão created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
