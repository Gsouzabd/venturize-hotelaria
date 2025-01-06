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
        th, td {
            min-width: 100px;
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
        th:last-child, td:last-child {
            width:100px !important;
            padding: 0px !important;
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
        <p><strong>Cliente:</strong> {{ $reserva->clienteResponsavel ? $reserva->clienteResponsavel->nome : $reserva->clienteSolicitante->nome }}</p>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Origem</th>

                    <th>Descrição</th>
                    <th>Valor Unitário</th>
                    <th>Quantidade</th>
                    <th>Total</th>
                    <th>Data</th>

                </tr>
            </thead>
            <tbody>
                <h2>Hospedagem</h2>

                <tr style="d-flex justify-content-between">
                    <TD>Reserva</TD>

                    <td>Quarto {{ $reserva->quarto->numero .' - '.  $reserva->quarto->classificacao}}</td>
                    <td>R$ {{ number_format($reserva->total, 2, ',', '.') }}</td>
                    <td>1</td>
                    <td>R$ {{ number_format($reserva->total, 2, ',', '.') }}</td>
                    <td >
                        Checkin: {{ \Carbon\Carbon::parse($reserva->data_checkin)->format('d/m/Y') }} 
                        <br/> 
                        Checkout: {{ \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y') }}
                    </td>
                </tr>

                <h2>Consumo</h2>


                @foreach ($itensConsumidos as $item)
                    <tr>
                        <td>{{ $item['pedido']->pedido_apartamento ? 'Apartamento' : 'Bar'}} <br/> Pedido #{{ $item['pedido']->id }} </td>
                        <td>{{ $item['produto'] }}</td>
                        <td>{{ $item['quantidade'] }}</td>
                        <td>R$ {{ number_format($item['valor_unitario'], 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($item['total'], 2, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($item['data_adicao'])->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
              
                <h2>Total</h2>
                <tr>
                    <td>Consumo</td>
                    <td>R$ {{ number_format($totalConsumo, 2, ',', '.') }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>  

                </tr>
                <tr>
                    <td>Taxa de Serviço</td>
                    <td>
                        @if (is_string($totalTaxaServicoConsumoConsumo))
                            {{ $totalTaxaServicoConsumoConsumo }}
                        @else
                        R$ {{ number_format( $totalTaxaServicoConsumoConsumo, 2, ',', '.') }}

                        @endif
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>  

                <tr>
                    <td>Reserva</td>
                    <td>R$ {{ number_format($reserva->total, 2, ',', '.') }}</td> 
                    <td></td>
                    <td></td>
                    <td></td>  
                    <td></td>  

                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td>
                        <strong>
                            R$ {{ number_format($totalConsumo + (!is_string($totalTaxaServicoConsumoConsumo) ? $totalTaxaServicoConsumoConsumo : 0) + $reserva->total, 2, ',', '.') }}
                        </strong>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>  

                </tr>

            </tbody>
        </table>
    </div>
</body>
</html>