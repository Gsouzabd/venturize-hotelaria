<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DespesaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');
        $rules = [
            'numero_nota_fiscal' => [
                'required',
                'string',
                'max:255',
                Rule::unique('despesas', 'numero_nota_fiscal')->ignore($id),
            ],
            'descricao' => 'required|string|max:1000',
            'data' => 'required|date_format:d/m/Y|before_or_equal:today',
            'valor_total' => 'required|numeric|min:0.01',
            'arquivo_nota' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'observacoes' => 'nullable|string',
            'rateios' => 'required|array|min:1',
            'rateios.*.categoria_despesa_id' => 'nullable|exists:categorias_despesas,id',
            'rateios.*.valor' => 'required|numeric|min:0.01',
            'rateios.*.observacoes' => 'nullable|string',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'numero_nota_fiscal.required' => 'O número da nota fiscal é obrigatório.',
            'numero_nota_fiscal.unique' => 'Já existe uma despesa com este número de nota fiscal.',
            'descricao.required' => 'A descrição da despesa é obrigatória.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'data.required' => 'A data é obrigatória.',
            'data.before_or_equal' => 'A data não pode ser futura.',
            'valor_total.required' => 'O valor total é obrigatório.',
            'valor_total.min' => 'O valor total deve ser maior que zero.',
            'rateios.required' => 'É necessário adicionar pelo menos um rateio.',
            'rateios.min' => 'É necessário adicionar pelo menos um rateio.',
            'rateios.*.valor.required' => 'O valor do rateio é obrigatório.',
            'rateios.*.valor.min' => 'O valor do rateio deve ser maior que zero.',
            'rateios.*.categoria_despesa_id.exists' => 'A categoria selecionada não existe.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $valorTotal = $this->input('valor_total');
            $rateios = $this->input('rateios', []);
            
            if (is_array($rateios) && count($rateios) > 0) {
                $somaRateios = array_sum(array_column($rateios, 'valor'));
                $diferenca = abs($valorTotal - $somaRateios);
                
                if ($diferenca > 0.01) {
                    $validator->errors()->add(
                        'rateios',
                        'A soma dos valores rateados (' . number_format($somaRateios, 2, ',', '.') . ') deve ser igual ao valor total da nota (' . number_format($valorTotal, 2, ',', '.') . ').'
                    );
                }
            }
        });
    }
}

