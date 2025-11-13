<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\GrupoUsuario;
use Illuminate\Database\Seeder;

class UsuarioAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar o grupo de usuário administrador
        $grupoAdmin = GrupoUsuario::where('nome', 'Administrador')->first();
        
        // Se não existir o grupo, criar
        if (!$grupoAdmin) {
            $grupoAdmin = GrupoUsuario::create(['nome' => 'Administrador']);
        }
        
        // Criar o usuário administrador
        Usuario::firstOrCreate(
            ['email' => 'danilo@pousada.com.br'],
            [
                'nome' => 'danilo',
                'senha' => bcrypt('admin'), // Usando o campo correto 'senha' em vez de 'password'
                'tipo' => 'administrador',
                'grupo_usuario_id' => $grupoAdmin->id,
                'fl_ativo' => true,
            ]
        );
    }
}