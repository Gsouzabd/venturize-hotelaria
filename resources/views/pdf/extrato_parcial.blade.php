<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato da Reserva</title>
    <style>
        body {
            font-family: 'Courier', monospace;
            font-size: 10px;
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
            min-width: 50px;
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
        <h3>Consumo</h3>
        <h5>Extrato Parcial</h5>
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
                    <th>Operador</th>
                    <th>Data</th>

                </tr>
            </thead>
            <tbody>

                <h2>Consumo</h2>


                 @foreach ($pedido->itens as $item)
                    {{-- @php dd($item); @endphp --}}
                    <tr>
                        <td>{{ $item->pedido->pedido_apartamento ? 'Apartamento' : 'Bar'}} <br/> Pedido #{{ $item->pedido->id }} </td>
                        <td>{{ $item->produto->descricao }}</td>
                        <td>{{ $item->quantidade }}</td>
                        <td>R$ {{ number_format($item->preco, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($item->preco * $item->quantidade , 2, ',', '.') }}</td>
                        <td>{{ $item->operador ? $item->operador->nome : '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
              
                <h2>Total</h2>
                <tr>
                    @php 
                        $totalConsumo = $pedido->total;
                        $totalTaxaServicoConsumoConsumo = $pedido->taxa_servico;
                        @endphp
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
                    <td><strong>Total</strong></td>
                    <td>
                        <strong>
                            R$ {{ number_format($totalConsumo + (!is_string($totalTaxaServicoConsumoConsumo) ?? 0), 2, ',', '.') }}
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