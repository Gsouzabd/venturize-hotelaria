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

        // Verificar se já existe um pagamento para a reserva
        $pagamento = Pagamento::where('reserva_id', $reservaId)->first();

        if ($pagamento) {
            // Atualizar pagamento existente
            $pagamento->valor_pago = $valorPago;
            $pagamento->valor_total = $valorTotal;
            $pagamento->valores_recebidos = json_encode($valoresRecebidos);
            $pagamento->save();
        } else {
            // Criar novo pagamento
            $pagamento = new Pagamento();
            $pagamento->reserva_id = $reservaId;
            $pagamento->valor_pago = $valorPago;
            $pagamento->valor_total = $valorTotal;
            $pagamento->valores_recebidos = json_encode($valoresRecebidos);
            $pagamento->save();
        }
    }
}