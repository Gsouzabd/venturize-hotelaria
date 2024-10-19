<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cupom de Cancelamento</title>
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
        .strong {
            font-weight: bold;
        }
        .signature {
            margin-top: 20px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            width: 80%;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="cupom">
        <h5>Cupom de Cancelamento</h5>
        <p><strong>Pedido ID:</strong> {{ $pedido->id }}</p>
        <p><strong>N° Mesa:</strong> {{ $pedido->mesa->numero }}</p>
        <p><strong>Status Mesa:</strong> {{ $pedido->status }}</p>
        <p><strong>Reserva ID:</strong> {{ $pedido->reserva->id }}</p>
        <p><strong>N° Quarto:</strong> {{ $pedido->reserva->quarto->numero }}</p>
        <p><strong>Cliente:</strong> {{ $pedido->cliente->nome }}</p>
        <hr>
        <h5>Itens Cancelados</h5>
        @foreach ($itensCancelados as $item)
            <div class="item">
                <p>{{ $item['descricao'] }} - R$ {{ number_format($item['preco'], 2, ',', '.') }} x {{ $item['quantidade'] }}</p>
            </div>
        @endforeach
        <div class="signature">
            <p>Assinatura do Cliente:</p>
            <div class="signature-line"></div>
        </div>
    </div>
</body>
</html>