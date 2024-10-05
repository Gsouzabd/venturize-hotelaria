<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    protected $fillable = [
        'reserva_id',
        'valor_pago',
        'valor_total',
        'valor_sinal',      // Caso o pagamento seja parcial
        'status_pagamento', // Pago, Parcialmente Pago, Pendente
        'data_pagamento',
        'valores_recebidos', // Campo para armazenar valores recebidos como JSON
    ];

    const METODOS_PAGAMENTO = [
        'PIX' => [
            'label' => 'Pix',
            'submetodos' => [
                'PIX_SITE' => 'Pix Site',
                'PIX_SICOOB' => 'Banco Sicoob',
                'PIX_BB' => 'Banco do Brasil',
            ],
        ],
        'CARTAO' => [
            'label' => 'Cartão',
            'submetodos' => [
                'CARTAO_DEBITO_CIELO' => 'Débito Cielo',
                'CARTAO_CREDITO_VISTA_CIELO' => 'Crédito à Vista Cielo',
                'CARTAO_CREDITO_VISTA_SITE' => 'Crédito à Vista Site',
                'CARTAO_CREDITO_PARCELADO_CIELO' => 'Crédito Parcelado Cielo',
                'CARTAO_CREDITO_PARCELADO_SITE' => 'Crédito Parcelado Site',
            ],
        ],
        'DINHEIRO' => [
            'label' => 'Dinheiro',
            'submetodos' => [],
        ],
        'VALE' => [
            'label' => 'Vale',
            'submetodos' => [],
        ],
        'FATURADO' => [
            'label' => 'Faturado',
            'submetodos' => [],
        ],
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