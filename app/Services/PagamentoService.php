<?php

namespace App\Services;

use App\Models\Pagamento;
use Carbon\Carbon;

class PagamentoService
{
    /**
     * Salva os pagamentos de uma reserva.
     *
     * @param  int  $reservaId
     * @param  array  $pagamentos  Array de objetos [{data, valor, metodo, submetodo, observacao}, ...]
     * @param  float  $valorTotal
     * @param  string|null  $statusPagamento
     */
    public function salvarPagamentos($reservaId, array $pagamentos, $valorTotal, $statusPagamento = null)
    {
        $valorPago = 0;
        $ultimaData = null;

        foreach ($pagamentos as $item) {
            $valorPago += (float) ($item['valor'] ?? 0);

            $dataItem = $item['data'] ?? null;
            if ($dataItem) {
                try {
                    $dataCarbon = $dataItem instanceof Carbon ? $dataItem : Carbon::parse($dataItem);
                    if (! $ultimaData || $dataCarbon->gt($ultimaData)) {
                        $ultimaData = $dataCarbon;
                    }
                } catch (\Exception $e) {
                    // ignora data inválida, mantém fallback
                }
            }
        }

        $dataPagamento = $ultimaData ?? now();
        $pagamentosJson = json_encode(array_values($pagamentos));

        $payload = [
            'valores_recebidos' => $pagamentosJson,
            'valor_pago' => $valorPago,
            'data_pagamento' => $dataPagamento,
            'valor_total' => $valorTotal,
        ];

        if ($statusPagamento !== null) {
            $payload['status_pagamento'] = $statusPagamento;
        }

        $pagamento = Pagamento::where('reserva_id', $reservaId)->first();

        if ($pagamento) {
            $pagamento->update($payload);
        } else {
            Pagamento::create(array_merge(['reserva_id' => $reservaId], $payload));
        }
    }
}
