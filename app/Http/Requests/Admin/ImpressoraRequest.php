<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImpressoraRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id') ?? $this->route('id');

        return [
            'nome' => 'required|string|max:255|unique:impressoras,nome,' . ($id ?? 'NULL'),
            'ip' => 'required|ip',
            'porta' => 'required|integer|min:1|max:65535',
            'tipo' => 'required|in:termica,convencional',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
            'ordem' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome da impressora é obrigatório.',
            'nome.unique' => 'Já existe uma impressora com este nome.',
            'nome.max' => 'O nome da impressora não pode ter mais de 255 caracteres.',
            'ip.required' => 'O IP da impressora é obrigatório.',
            'ip.ip' => 'O IP informado não é válido.',
            'porta.required' => 'A porta é obrigatória.',
            'porta.integer' => 'A porta deve ser um número inteiro.',
            'porta.min' => 'A porta deve ser no mínimo 1.',
            'porta.max' => 'A porta deve ser no máximo 65535.',
            'tipo.required' => 'O tipo da impressora é obrigatório.',
            'tipo.in' => 'O tipo deve ser "térmica" ou "convencional".',
            'ordem.integer' => 'A ordem deve ser um número inteiro.',
            'ordem.min' => 'A ordem deve ser no mínimo 0.',
        ];
    }
}

