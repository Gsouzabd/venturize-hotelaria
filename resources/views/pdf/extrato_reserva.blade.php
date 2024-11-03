<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato da Reserva</title>
    <style>
        body {
            font-family: 'Courier', monospace;
            font-size: 12px;
            background-color: #fff;
        }
        .cupom {
            width: 100%;
            max-width: 210mm; /* A4 width */
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
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="cupom">
        <h3>Reserva</h3>
        <h5>Detalhes da Reserva</h5>
        <p style="text-align:center; font-size: 10px;">
            Data: {{ \Carbon\Carbon::now()->setTimezone('America/Sao_Paulo')->format('d/m/Y') }}
        </p>        
        <br/>
        <p><strong>N° Reserva:</strong> {{ $reserva->id }}</p>
        <p><strong>N° Quarto:</strong> {{ $reserva->quarto->numero }}</p>
        <p><strong>Classificação:</strong> {{ $reserva->quarto->classificacao }}</p>
        <p><strong>Cliente:</strong> {{ $reserva->clienteSolicitante->nome }}</p>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Origem</th>
                    <th></th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr style="d-flex justify-content-between">
                    <td>Reserva - Quarto {{ $reserva->quarto->numero .' - '.  $reserva->quarto->classificacao}}</td>
                    <td></td>
                    <td>R$ {{ number_format($reserva->total, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Consumo Bar</td>
                    <td></td>
                    <td>R$ {{ number_format($totalConsumoBar, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Consumo Bar - Taxa de Serviço</td>
                    <td></td>
                    <td>R$ {{ number_format($totalTaxaServicoConsumoBar, 2, ',', '.') }}</td>
                </tr>
                @foreach ($reserva->pedidos as $pedido)
                    <tr>
                        <td>Pedido #{{ $pedido->id }} - Mesa {{ $pedido->mesa->numero }}</td>
                        <td></td>
                        <td>R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>