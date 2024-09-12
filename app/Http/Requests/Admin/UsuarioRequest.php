<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

class UsuarioRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $rules = [
            'nome' => 'required',
            'email' => 'required|email|unique:usuarios',
            'tipo' => 'required',
            'senha' => 'required|min:6|confirmed',
        ];

        if ($this->method() === 'PUT') {
            $rules['email'] = 'required|email|unique:usuarios,email,' . $this->get('id');
            $rules['senha'] = 'nullable|min:6|required_with:senha_confirmation|confirmed';
        }

        return $rules;
    }
}
