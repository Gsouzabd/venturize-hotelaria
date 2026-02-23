<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DayUsePlanoPreco;

class DayUsePlanoPrecoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plano padrão de Day Use
        DayUsePlanoPreco::create([
            'data_inicio' => null,
            'data_fim' => null,
            'is_default' => true,
            'preco_segunda' => 150.00,
            'preco_terca' => 150.00,
            'preco_quarta' => 150.00,
            'preco_quinta' => 150.00,
            'preco_sexta' => 200.00,
            'preco_sabado' => 200.00,
            'preco_domingo' => 200.00,
            'preco_cafe' => 35.00,
        ]);
    }
}

