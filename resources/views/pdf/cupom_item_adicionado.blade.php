<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom do Pedido</title>
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
    </style>
</head>
<body>
    <div class="cupom">
        <h3>Bar</h3>
        <h5>Item Adicionado</h5>
        <p style="text-align:center; font-size: 10px;">
            Data e Hora: {{ \Carbon\Carbon::now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s') }}
        </p>        
        <br/>
        <p><strong>N° Pedido:</strong> {{ $pedido->id }}</p>
        <p><strong>N° Mesa:</strong> {{ $pedido->mesa->numero }}</p>
        {{-- <p><strong>Status Mesa:</strong> {{ $pedido->status }}</p> --}}
        <p><strong>N° Reserva:</strong> {{ $pedido->reserva->id }}</p>
        <p><strong>N° Quarto:</strong> {{ $pedido->reserva->quarto->numero }}</p>
        <p><strong>Cliente:</strong> {{ $pedido->cliente->nome }}</p>
        <hr>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px; font-weight:300;">Qtde</th>
                    <th style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 8px; font-weight:300;">Produto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($novosItens as $item)
                    <tr>
                        <td style="padding: 8px;">{{ $item['quantidade'] }}</td>
                        <td style="padding: 8px;">{{ $item['descricao'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>