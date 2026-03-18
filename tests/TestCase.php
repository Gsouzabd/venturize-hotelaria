<?php

namespace Tests;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function adminLogin(): static
    {
        $usuario = Usuario::factory()->create();
        return $this->actingAs($usuario, 'admin');
    }
}
