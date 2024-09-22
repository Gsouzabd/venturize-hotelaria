<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cnpj implements Rule
{
    public function passes($attribute, $value)
    {
        return $this->validateCnpj($value);
    }

    public function message()
    {
        return 'O CNPJ informado não é válido.';
    }

    private function validateCnpj($cnpj)
    {
        // Remover caracteres especiais
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verificar se o CNPJ tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcular os dígitos verificadores
        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cnpj[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}