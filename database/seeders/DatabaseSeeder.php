<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\QuartoSeeder;
use Database\Seeders\ClienteSeeder;
use Database\Seeders\LocaisEstoque;
use Database\Seeders\ProdutoSeeder;
use Database\Seeders\ReservaSeeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\GrupoUsuarioSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {



        $this->call([
            ProdutoSeeder::class,
            LocaisEstoqueTableSeeder::class,
            EstoqueTableSeeder::class,
        ]);

    }
}
