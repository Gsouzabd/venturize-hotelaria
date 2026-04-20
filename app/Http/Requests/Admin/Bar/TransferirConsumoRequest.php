<?php

namespace App\Http\Requests\Admin\Bar;

use Illuminate\Foundation\Http\FormRequest;

class TransferirConsumoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reserva_destino_id' => 'required|integer|exists:reservas,id',
            'itens' => 'required|array|min:1',
            'itens.*.produto_id' => 'required|integer|exists:produtos,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ];
    }
}
