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
        return [
            'tipo_reserva' => 'required|string',
            'nome' => 'required|string|max:255',
            'cpf' => ['required', 'string', 'max:14', new Cpf],
            'rg' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date',
            'email' => 'required|email|max:255',
            'email_faturamento' => 'nullable|email|max:255',
            'telefone' => 'required|string|max:20',
            'celular' => 'nullable|string|max:20',
            'nome_fantasia_faturamento' => 'nullable|string|max:255',
            'empresa_faturamento_id' => 'nullable|integer|exists:empresas,id',
            'cnpj_faturamento' => ['nullable', 'string', 'max:18', new Cnpj],
            'nome_fantasia_solicitante' => 'nullable|string|max:255',
            'empresa_solicitante_id' => 'nullable|integer|exists:empresas,id',
            'cnpj_solicitante' => ['nullable', 'string', 'max:18', new Cnpj],
            'observacoes' => 'nullable|string',
            'observacoes_internas' => 'nullable|string',
            'data_entrada' => 'required|date_format:d/m/Y',
            'data_saida' => 'required|date_format:d/m/Y|after_or_equal:data_entrada',
            'tipo_quarto' => 'nullable|string|max:255',
            'apartamentos' => 'required|integer|min:1',
            'adultos' => 'required|integer|min:1',
            'criancas' => 'required|integer|min:0',
            'quartos' => 'required|array|min:1',
            'quartos.*.numero' => 'required|string|max:10',
            'quartos.*.andar' => 'required|string|max:50',
            'quartos.*.classificacao' => 'required|string|max:50',
            'quartos.*.responsavel_nome' => 'required|string|max:255',
            'quartos.*.responsavel_cpf' => ['required', 'string', 'max:14', new Cpf],
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