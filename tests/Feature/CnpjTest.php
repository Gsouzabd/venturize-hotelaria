<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Rules\Cnpj;

class CnpjTest extends TestCase
{
    /**
     * Testa CNPJs válidos.
     *
     * @return void
     */
    public function testValidCnpjs()
    {
        $rule = new Cnpj();

        $validCnpjs = [
            '12.345.678/0001-95',
            '11.222.333/0001-81',
            '45.987.654/0001-32',
            '63.264.233/0001-12',
            '12345678000195',
            '11222333000181',
            '45987654000132',
            '63264233000112',
        ];

        foreach ($validCnpjs as $cnpj) {
            $this->assertTrue($rule->passes('cnpj', $cnpj), "Failed asserting that $cnpj is valid.");
        }
    }

    /**
     * Testa CNPJs inválidos.
     *
     * @return void
     */
    public function testInvalidCnpjs()
    {
        $rule = new Cnpj();

        $invalidCnpjs = [
            '12.345.678/0001-96', // Invalid check digits
            '11.222.333/0001-82', // Invalid check digits
            '45.987.654/0001-33', // Invalid check digits
            '12345678000196',     // Invalid check digits
            '11222333000182',     // Invalid check digits
            '45987654000133',     // Invalid check digits
            '11111111111111',     // All digits are the same
            '123',                // Too short
            '123456789012345',    // Too long
            '12.345.678/0001-9a', // Contains non-numeric character
        ];

        foreach ($invalidCnpjs as $cnpj) {
            $this->assertFalse($rule->passes('cnpj', $cnpj), "Failed asserting that $cnpj is invalid.");
        }
    }
}