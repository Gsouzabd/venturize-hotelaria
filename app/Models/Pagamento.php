<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'reserva_id',
        'metodo_pagamento',  // pix, dinheiro, cartão de crédito, transferência
        'valor_pago',
        'valor_total',
        'valor_sinal',      // Caso o pagamento seja parcial
        'status_pagamento', // Pago, Parcialmente Pago, Pendente
        'data_pagamento',
    ];

    const METODOS_PAGAMENTO = [
        'PIX' => 'Pix',
        'DINHEIRO' => 'Dinheiro',
        'CARTAO_CREDITO' => 'Cartão de Crédito',
        'TRANSFERENCIA' => 'Transferência Bancária',
    ];

    const STATUS_PAGAMENTO = [
        'PAGO' => 'Pago',
        'PARCIAL' => 'Parcialmente Pago',
        'PENDENTE' => 'Pendente',
    ];

    // Relacionamentos
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}
