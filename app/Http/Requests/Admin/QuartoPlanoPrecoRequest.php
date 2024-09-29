<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class QuartoPlanoPrecoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permite que a requisição seja autorizada
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quarto_id' => 'required|exists:quartos,id',
            'data_inicio' => 'nullable',
            'data_fim' => 'nullable',
            'is_default' => 'boolean',
            'preco_segunda' => 'required|numeric',
            'preco_terca' => 'required|numeric',
            'preco_quarta' => 'required|numeric',
            'preco_quinta' => 'required|numeric',
            'preco_sexta' => 'required|numeric',
            'preco_sabado' => 'required|numeric',
            'preco_domingo' => 'required|numeric',
        ];
    }
}