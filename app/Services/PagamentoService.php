<?php

namespace App\Services;

use App\Models\Pagamento;

class PagamentoService
{
    public function salvarPagamentos($reservaId, $valoresRecebidos, $valorPago, $valorTotal)
    {
        // Garantir que os valores estão no formato correto
        $valorPago = str_replace(',', '.', $valorPago);
        $valorTotal = str_replace(',', '.', $valorTotal);

        $pagamento = new Pagamento();
        $pagamento->reserva_id = $reservaId;
        $pagamento->valor_pago = $valorPago;
        $pagamento->valor_total = $valorTotal;
        $pagamento->valores_recebidos = json_encode($valoresRecebidos);
        $pagamento->save();
    }
}