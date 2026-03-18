<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'tipo'       => 'Pessoa Física',
            'estrangeiro'=> 'Não',
            'sexo'       => fake()->randomElement(['M', 'F']),
            'nome'       => fake()->name(),
            'cpf'        => fake()->unique()->numerify('###########'),
            'data_nascimento' => fake()->date(),
            'email'      => fake()->unique()->safeEmail(),
        ];
    }
}
