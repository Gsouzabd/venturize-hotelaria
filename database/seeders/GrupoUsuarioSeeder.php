<?php

namespace Database\Seeders;

use App\Models\Permissao;
use App\Models\GrupoUsuario;
use App\Models\Usuario; // Certifique-se de importar o model Usuario
use Illuminate\Database\Seeder;

class GrupoUsuarioSeeder extends Seeder
{
    public function run()
    {
        // Criar Grupos de Usuários
        $administrador = GrupoUsuario::create(['nome' => 'Administrador']);
        $gerente = GrupoUsuario::create(['nome' => 'Gerente']);

        // Verificar se os grupos foram criados corretamente
        if (!$administrador || !$gerente) {
            dd('Erro ao criar grupos de usuários!');
        }

        // Definir Permissões
        $permissoes = [
            'visualizar_relatorios',
            'gerenciar_reservas',
            'gerenciar_usuarios',
        ];

        foreach ($permissoes as $permissaoNome) {
            // Criar a permissão
            $permissao = Permissao::create(['nome' => $permissaoNome]);

            // Verificar se a permissão foi criada corretamente
            if (!$permissao) {
                dd('Erro ao criar permissão: ' . $permissaoNome);
            }

            // Associar permissões ao grupo Administrador
            $administrador->permissoes()->attach($permissao->id);

            // Associar ao grupo Gerente (agora incluindo 'gerenciar_usuarios')
            $gerente->permissoes()->attach($permissao->id);
        }

        // Relacionar o usuário de ID 1 ao grupo Gerente
        $usuario = Usuario::find(1);
        if ($usuario) {
            $usuario->grupo_usuario_id = $gerente->id;
            $usuario->save();
        } else {
            dd('Usuário com ID 1 não encontrado.');
        }
    }
}
