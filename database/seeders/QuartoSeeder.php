<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quarto;

class QuartoSeeder extends Seeder
{
    public function run()
    {
        Quarto::create([
            'andar' => 'Térreo',
            'numero' => 101,
            'ramal' => '101',
            'posicao_quarto' => 'Frente',
            'quantidade_cama_casal' => 1,
            'quantidade_cama_solteiro' => 1,
            'classificacao' => 'Camará',
            'acessibilidade' => 'Não',
            'inativo' => 'Não',
        ]);

        Quarto::create([
            'andar' => '1º Andar',
            'numero' => 201,
            'ramal' => '201',
            'posicao_quarto' => 'Lateral',
            'quantidade_cama_casal' => 1,
            'quantidade_cama_solteiro' => 2,
            'classificacao' => 'Embaúba',
            'acessibilidade' => 'Sim',
            'inativo' => 'Não',
        ]);
    }
}
