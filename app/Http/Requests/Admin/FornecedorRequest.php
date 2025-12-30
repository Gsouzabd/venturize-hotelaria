<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FornecedorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fornecedores', 'nome')->ignore($id),
            ],
            'cnpj' => 'nullable|string|max:18',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'endereco' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome do fornecedor é obrigatório.',
            'nome.unique' => 'Já existe um fornecedor com este nome.',
            'nome.max' => 'O nome do fornecedor não pode ter mais de 255 caracteres.',
            'email.email' => 'O e-mail informado não é válido.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
            'cnpj.max' => 'O CNPJ não pode ter mais de 18 caracteres.',
            'telefone.max' => 'O telefone não pode ter mais de 20 caracteres.',
        ];
    }
}
