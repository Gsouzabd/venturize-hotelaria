<?php

namespace Database\Factories;

use App\Models\Acompanhante;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcompanhanteFactory extends Factory
{
    protected $model = Acompanhante::class;

    public function definition(): array
    {
        return [
            'reserva_id'     => Reserva::factory(),
            'nome'           => fake()->name(),
            'cpf'            => fake()->unique()->numerify('###########'),
            'data_nascimento'=> fake()->date(),
            'tipo'           => 'acompanhante',
            'email'          => fake()->unique()->safeEmail(),
            'telefone'       => fake()->phoneNumber(),
        ];
    }
}
