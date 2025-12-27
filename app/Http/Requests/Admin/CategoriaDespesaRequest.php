<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaDespesaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'nome' => 'required|string|max:255|unique:categorias_despesas,nome,' . $id,
            'descricao' => 'nullable|string',
            'fl_ativo' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome da categoria é obrigatório.',
            'nome.unique' => 'Já existe uma categoria com este nome.',
            'nome.max' => 'O nome da categoria não pode ter mais de 255 caracteres.',
        ];
    }
}

