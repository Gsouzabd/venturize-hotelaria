<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReservaRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust as necessary
    }

    public function rules()
    {
        return [
            'tipo_reserva' => 'string',            
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'max:14'],
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|string',
            'email' => 'required|email|max:255',
            'email_faturamento' => 'nullable|email|max:255',
            'telefone' => 'required|string|max:20',
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
            'quartos' => 'required|array|min:1',
            'quartos.*.numero' => 'required|string|max:10',
            'quartos.*.andar' => 'required|string|max:50',
            'quartos.*.classificacao' => 'required|string|max:50',
            'quartos.*.data_checkin' => 'required',
            'quartos.*.data_checkout' => 'required|after_or_equal:quartos.*.data_checkin',
            'quartos.*.responsavel_nome' => 'nullable|string|max:255',
            'quartos.*.responsavel_cpf' => ['nullable','string', 'max:14'],
        ];
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