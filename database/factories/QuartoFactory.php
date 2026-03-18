<?php

namespace Database\Factories;

use App\Models\Quarto;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuartoFactory extends Factory
{
    protected $model = Quarto::class;

    public function definition(): array
    {
        return [
            'andar'                    => (string) fake()->numberBetween(1, 5),
            'numero'                   => fake()->unique()->numberBetween(100, 599),
            'ramal'                    => null,
            'posicao_quarto'           => fake()->randomElement(['Frente', 'Fundos', 'Lateral']),
            'classificacao'            => fake()->randomElement(['Camará', 'Embaúba']),
            'quantidade_cama_casal'    => 1,
            'quantidade_cama_solteiro' => 0,
            'acessibilidade'           => 'Não',
            'inativo'                  => 'Não',
        ];
    }
}
