<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DisponibilidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data_entrada' => 'required|date_format:d/m/Y',
            'data_saida' => 'required|date_format:d/m/Y|after:data_entrada',
            'apartamentos' => 'required|integer|min:1',
            'adultos' => 'required|integer|min:1',
            'criancas_ate_7' => 'nullable|integer|min:0',
            'criancas_mais_7' => 'nullable|integer|min:0',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422));
    }
}