<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\GrupoUsuarioSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Usuario::create([
            'nome' => 'Gerente',
            'email' => 'test@example.com',
            'senha' => bcrypt('teste@123'),
            'tipo' => 'gerente',
            'fl_ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        $this->call(GrupoUsuarioSeeder::class);

    }
}
