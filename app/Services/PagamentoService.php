<?php

namespace App\Services;

use App\Models\Pagamento;

class PagamentoService
{
    public function salvarPagamentos($reservaId, $pagamentosJson, $valorPago, $valorTotal)
    {
        $pagamento = Pagamento::where('reserva_id', $reservaId)->first();

        if ($pagamento) {
            // Update existing payment record
            $pagamento->update([
                'valores_recebidos' => $pagamentosJson,
                'valor_pago' => $valorPago,
                'data_pagamento' => now(),
                'valor_total' => $valorTotal,
            ]);
        } else {
            // Create new payment record
            Pagamento::create([
                'reserva_id' => $reservaId,
                'valores_recebidos' => $pagamentosJson,
                'valor_pago' => $valorPago,
                'data_pagamento' => now(),
                'valor_total' => $valorTotal,
            ]);
        }
    }
}