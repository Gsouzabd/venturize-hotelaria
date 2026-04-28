<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pagamentos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 12px; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #333; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 3px; text-align: left; }
        th { background: #eee; font-weight: bold; }
        td.num { text-align: right; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        .badge-pago { color: #155724; }
        .badge-parcial { color: #856404; }
        .badge-pendente { color: #383d41; }
    </style>
</head>
<body>
    <h1>Relatório de Pagamentos de Reservas</h1>
    <div class="meta">
        Gerado em: {{ $geradoEm }}<br>
        @if(!empty($filters['tipo_pagamento']))
            Tipo de pagamento: {{ $tiposPagamento[$filters['tipo_pagamento']]['label'] ?? $filters['tipo_pagamento'] }}<br>
        @endif
        @if(!empty($filters['tipo_quarto']))
            Tipo de quarto: {{ $filters['tipo_quarto'] }}<br>
        @endif
        @if(!empty($filters['nome']))
            Hóspede: {{ $filters['nome'] }}<br>
        @endif
        @if(!empty($filters['data_checkin_inicial']) || !empty($filters['data_checkin_final']))
            Check-in: {{ $filters['data_checkin_inicial'] ?? '—' }} até {{ $filters['data_checkin_final'] ?? '—' }}<br>
        @endif
        @if(!empty($filters['data_checkout_inicial']) || !empty($filters['data_checkout_final']))
            Check-out: {{ $filters['data_checkout_inicial'] ?? '—' }} até {{ $filters['data_checkout_final'] ?? '—' }}<br>
        @endif
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hóspede</th>
                <th>Quarto</th>
                <th>Tipo Quarto</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Tipo Pagamento</th>
                <th>Valor Pago</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservas as $reserva)
                @php
                    use App\Models\Pagamento;
                    $cliente = $reserva->clienteResponsavel ?? $reserva->clienteSolicitante;
                    $pagamento = $reserva->pagamentos->first();
                    $valorPago = $reserva->pagamentos->sum('valor_pago');

                    $metodosLabel = '';
                    if ($pagamento && $pagamento->valores_recebidos) {
                        $valores = is_array($pagamento->valores_recebidos)
                            ? $pagamento->valores_recebidos
                            : json_decode($pagamento->valores_recebidos, true) ?? [];
                        $labels = [];
                        foreach (array_keys($valores) as $chave) {
                            $key = explode('-', $chave)[0];
                            foreach (Pagamento::METODOS_PAGAMENTO as $catKey => $cat) {
                                if ($key === $catKey) { $labels[] = $cat['label']; break; }
                                foreach ($cat['submetodos'] as $subKey => $subLabel) {
                                    if ($key === $subKey) { $labels[] = $subLabel; break 2; }
                                }
                            }
                        }
                        $metodosLabel = implode(', ', array_unique($labels));
                    }

                    $status = $pagamento->status_pagamento ?? '';
                    $statusClass = $status === 'PAGO' ? 'badge-pago' : ($status === 'PARCIAL' ? 'badge-parcial' : 'badge-pendente');
                @endphp
                <tr>
                    <td>{{ $reserva->id }}</td>
                    <td>{{ $cliente->nome ?? '—' }}</td>
                    <td>{{ $reserva->quarto->numero ?? '—' }}</td>
                    <td>{{ $reserva->quarto->classificacao ?? '—' }}</td>
                    <td>{{ $reserva->data_checkin ? \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $reserva->data_checkout ? \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $metodosLabel ?: '—' }}</td>
                    <td class="num">R$ {{ number_format($valorPago, 2, ',', '.') }}</td>
                    <td class="{{ $statusClass }}">{{ Pagamento::STATUS_PAGAMENTO[$status] ?? $status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
        @if($reservas->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right">Total:</td>
                    <td class="num">R$ {{ number_format($totalValor, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
