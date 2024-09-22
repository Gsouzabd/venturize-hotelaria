<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Rules\Cpf;

class CpfTest extends TestCase
{
    public function testValidCpf()
    {
        $rule = new Cpf();
        $this->assertTrue($rule->passes('cpf', '709.940.344-31'));
    }

    public function testInvalidCpf()
    {
        $rule = new Cpf();
        $this->assertFalse($rule->passes('cpf', '123.456.789-00'));
    }
}