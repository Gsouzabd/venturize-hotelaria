<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bar\Mesa;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar mesas do bar
        $mesas = [
            ['numero' => 1, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 2, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 3, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 4, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 5, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 6, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 7, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 8, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 9, 'status' => 'disponível', 'ativa' => true],
            ['numero' => 10, 'status' => 'disponível', 'ativa' => true],
        ];

        foreach ($mesas as $mesa) {
            Mesa::create($mesa);
        }
    }
}