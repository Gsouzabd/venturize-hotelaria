<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Cpf implements Rule
{
    protected $cpf;

    public function passes($attribute, $value)
    {
        $this->cpf = $value;
        return $this->validateCpf($value);
    }

    public function message()
    {
        return "O CPF informado ({$this->cpf}) não é válido.";
    }

    private function validateCpf($cpf)
    {
        // Remover caracteres especiais
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Verificar se o CPF tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verificar se todos os dígitos são iguais
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        return true;
    }
}