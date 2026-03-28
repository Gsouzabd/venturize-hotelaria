<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoUsuarioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->get('id');

        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupo_usuarios', 'nome')->ignore($id),
            ],
            'permissoes' => 'nullable|array',
            'permissoes.*' => 'exists:permissoes,id',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome do grupo é obrigatório.',
            'nome.unique' => 'Já existe um grupo com este nome.',
            'nome.max' => 'O nome do grupo não pode ter mais de 255 caracteres.',
            'permissoes.array' => 'As permissões devem ser uma lista.',
            'permissoes.*.exists' => 'Uma das permissões selecionadas é inválida.',
        ];
    }
}
