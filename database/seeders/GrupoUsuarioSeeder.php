<?php

namespace Database\Seeders;

use App\Models\Permissao;
use App\Models\GrupoUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class GrupoUsuarioSeeder extends Seeder
{
    public function run()
    {
        // Criar Grupos de Usuários
        $administrador = GrupoUsuario::firstOrCreate(['nome' => 'Administrador']);
        $gerente = GrupoUsuario::firstOrCreate(['nome' => 'Gerente']);

        // Criar todas as permissões definidas no config
        $permissoesConfig = config('app.enums.permissoes_plano', []);
        $permissaoIds = [];

        foreach ($permissoesConfig as $nome => $label) {
            $permissao = Permissao::firstOrCreate(['nome' => $nome]);
            $permissaoIds[] = $permissao->id;
        }

        // Associar todas as permissões ao grupo Administrador
        $administrador->permissoes()->sync($permissaoIds);

        // Associar todas as permissões ao grupo Gerente
        $gerente->permissoes()->sync($permissaoIds);

        // Relacionar o usuário de ID 1 ao grupo Administrador
        $usuario = Usuario::find(1);
        if ($usuario) {
            $usuario->grupo_usuario_id = $administrador->id;
            $usuario->save();
        }
    }
}
