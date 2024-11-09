<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom Parcial</title>
    <style>
        body {
            font-family: 'Courier', monospace;
            font-size: 12px;
            background-color: #fff;
        }
        .cupom {
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #000;
            border-bottom: 4px dashed #000;
        }
        h5, h3 {
            text-align: center;
            margin: 5px 0;
        }
        p, .item {
            margin: 5px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
        }
        .item p {
            margin: 0;
        }
        hr {
            border: 1px dashed #000;
        }
        strong {
            font-weight: 500;
        }
        td {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px;
            font-weight: 300;
        }
        td {
            padding: 8px;
        }
        .signature {
            margin-top: 20px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="cupom">
           @if ($pedido->pedido_apartamento)
            <h3>Apartamento</h3>
           @else
               <h3>Bar</h3>
        @endif  
        <h5>Parcial Atual do Pedido</h5>
        <p style="text-align:center; font-size: 10px;">
            Data e Hora: {{ \Carbon\Carbon::now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}
        </p>        
        <br/>
        <p><strong>N° Pedido:</strong> {{ $pedido->id }}</p>
        @if (!$pedido->pedido_apartamento)
            <p><strong>N° Mesa:</strong> {{ $pedido->mesa->numero }}</p>
        @endif        {{-- <p><strong>Status Mesa:</strong> {{ $pedido->status }}</p> --}}
        <p><strong>N° Reserva:</strong> {{ $pedido->reserva->id }}</p>
        <p><strong>N° Quarto:</strong> {{ $pedido->reserva->quarto->numero }}</p>
        <p><strong>Cliente:</strong> {{ $pedido->cliente->nome }}</p>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Qtde</th>
                    <th>Produto</th>
                    <th>Preço</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedido->itens as $item)
                    <tr>
                        <td>{{ $item->quantidade }}</td>
                        <td>{{ $item->produto->descricao }}</td>
                        <td>R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        @php
            $total = $pedido->total;
            $serviceFee = $total * 0.10;
            $totalWithServiceFee = $total + $serviceFee;
        @endphp
        <p><strong>Total Consumo:</strong> R$ {{ number_format($total, 2, ',', '.') }}</p>
        <p><strong>Taxa Serviço (10%):</strong> R$ {{ number_format($serviceFee, 2, ',', '.') }}</p>
        <p><strong>Total com Taxa de Serviço:</strong> <br/> R$ {{ number_format($totalWithServiceFee, 2, ',', '.') }}</p>
        <div class="signature">
            <p>Assinatura do Cliente:</p>
            <div class="signature-line"></div>
        </div>
    </div>
</body>
</html>