<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Cpf;
use App\Rules\Cnpj;

class ReservaRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Ajuste conforme necessário
    }

    public function rules()
    {
        $rules = [
            'tipo_reserva' => 'nullable|string|in:INDIVIDUAL,GRUPO,DAY_USE',
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'max:14'],
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'email_faturamento' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'nome_fantasia_faturamento' => 'nullable|string|max:255',
            'empresa_faturamento_id' => 'nullable|integer|exists:empresas,id',
            'cnpj_faturamento' => ['nullable', 'string', 'max:18', 'min:14'],

            'nome_fantasia_solicitante' => 'nullable|string|max:255',
            'empresa_solicitante_id' => 'nullable|integer|exists:empresas,id',
            'cnpj_solicitante' => ['nullable', 'string', 'max:18', 'min:14'],
            'observacoes' => 'nullable|string',
            'observacoes_internas' => 'nullable|string',
            'data_entrada' => 'required|date_format:d/m/Y',
            'data_saida' => 'required|date_format:d/m/Y|after_or_equal:data_entrada',
            'tipo_quarto' => 'nullable|string|max:255',
            'com_cafe' => 'sometimes|boolean',
        ];

        // Day Use não exige quartos; demais tipos exigem ao menos um quarto
        if ($this->input('tipo_reserva') === 'DAY_USE') {
            $rules['quartos'] = 'nullable|array';
        } else {
            $rules['quartos'] = 'required|array|min:1';
            $rules['quartos.*.responsavel_nome'] = 'nullable|string|max:255';
            $rules['quartos.*.responsavel_cpf'] = ['nullable', 'string', 'max:14'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'cpf.cpf' => 'O CPF informado não é válido.',
            'cnpj.cnpj' => 'O CNPJ informado não é válido.',
            'data_entrada.date_format' => 'A data de entrada deve estar no formato dd/mm/yyyy.',
            'data_saida.date_format' => 'A data de saída deve estar no formato dd/mm/yyyy.',
            'data_saida.after_or_equal' => 'A data de saída deve ser igual ou posterior à data de entrada.',
            'quartos.min' => 'Necessário Incluir ao menos 1 quarto',
        ];
    }
}