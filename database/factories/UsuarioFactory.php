<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nome'  => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'senha' => 'password', // hashed by setSenhaAttribute mutator
            'tipo'  => 'admin',
            'fl_ativo' => true,
        ];
    }
}
