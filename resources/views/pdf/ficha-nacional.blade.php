<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .full-width {
            width: 100%;
        }
        .half-width {
            width: 50%;
        }
        .third-width {
            width: 33%;
        }
        .lgpd-section {
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        POUSADA ALDEIA DOS CAMARAS<br>
        FICHA NACIONAL DE REGISTRO DE HÓSPEDES<br>
    </div>
    <p style="font-size: 10px;" class="text-center">Preenchimento Obrigatório - Deliberação Normativa 429 do Ministério do Esporte e Turismo - EMBRATUR</p>
    <p></p>
    <p class="text-center"><strong>Código da Hospedagem:</strong> {{ $reserva->id }}</p>
    <p class="text-center"><strong>Código da Reserva:</strong> {{ $reserva->id }}</p>

    <table>
        <tr>
            <td class="half-width"><strong>Nome Completo / Full Name:</strong> {{ $reserva->clienteResponsavel->nome ?? '       ' }}</td>
            <td class="half-width"><strong>Nome Social / Social Name:</strong> {{ $reserva->clienteResponsavel->nome ?? '       ' }}</td>
        </tr>
        <tr>
            <td class="half-width"><strong>E-mail:</strong> {{ $reserva->clienteResponsavel->email ?? '       ' }}</td>
            <td class="half-width"><strong>Número de Hóspedes / Number of Guests:</strong> {{ (int)($reserva->adultos ?? 0) + (int)($reserva->criancas_ate_7 ?? 0) + (int)($reserva->criancas_mais_7 ?? 0) }}</td>
        </tr>
        <tr>
            <td class="half-width"><strong>Telefone / Phone:</strong> {{ $reserva->clienteResponsavel->telefone ?? '       ' }}</td>
            <td class="half-width"><strong>Celular / Cellphone:</strong> {{ $reserva->clienteResponsavel->celular ?? '       ' }}</td>
            <td class="half-width"><strong>Profissão / Occupation:</strong> {{ $reserva->clienteResponsavel->profissao ?? '       ' }}</td>
        </tr>
        <tr>
            <td class="half-width"><strong>Data Nasc. / Birth Date:</strong> {{ $reserva->clienteResponsavel->data_nascimento ? \Carbon\Carbon::parse($reserva->clienteResponsavel->data_nascimento)->format('d/m/Y') : '       ' }}</td>
            <td class="half-width"><strong>Nacionalidade (País) / Citizenship (Country):</strong> {{ $reserva->clienteResponsavel->nacionalidade ?? '       ' }}</td>
            <td class="half-width"><strong>Gênero / Gender:</strong> {{ $reserva->clienteResponsavel->genero ?? '       ' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="full-width">
                <strong>Documento de Identidade / Travel Document:</strong><br/>
                Número / Number: {{ $reserva->clienteResponsavel->documento_identidade ?? '       ' }}<br/>
                Tipo / Type: {{ $reserva->clienteResponsavel->documento_tipo ?? '       ' }}<br/>
                Órgão Expedidor / Issuing Country: {{ $reserva->clienteResponsavel->orgao_expedidor ?? '       ' }}
            </td>
        </tr>
        <tr>
            <td class="half-width"><strong>CPF (Brazilian Document):</strong> {{ $reserva->clienteResponsavel->cpf ?? '       ' }}</td>
            <td colspan="2"></td> <!-- Add empty cells to balance the row -->
        </tr>
        
        <tr>
            <td class="half-width"><strong>Residência Permanente / Permanent Residence:</strong> {{ $reserva->clienteResponsavel->endereco ?? '       ' }}</td>

            <td class="half-width"><strong>CEP / Zip Code:</strong> {{ $reserva->clienteResponsavel->cep ?? '       ' }}</td>
            <td class="half-width"><strong>Bairro / District:</strong> {{ $reserva->clienteResponsavel->bairro ?? '       ' }}</td>
        </tr>
        <tr>
            <td class="half-width"><strong>Cidade / City:</strong> {{ $reserva->clienteResponsavel->cidade ?? '       ' }}</td>
            <td class="half-width"><strong>Estado / State:</strong> {{ $reserva->clienteResponsavel->estado ?? '       ' }}</td>
            <td class="half-width"><strong>País / Country:</strong> {{ $reserva->clienteResponsavel->pais ?? '       ' }}</td>
        </tr>

    </table>

    <table>
        <tr>
            <td class="half-width"><strong>Última Procedência / Arriving From:</strong></td>
            <td class="half-width"><strong>Próximo Destino / Next Destination:</strong></td>
        </tr>
        <tr>
            <td>{{ $reserva->ultima_procedencia ?? '       ' }}</td>
            <td>{{ $reserva->proximo_destino ?? '       ' }}</td>
        </tr>
    </table>
    

    <p class="section-title">Motivo da Viagem / Purpose of Trip</p>
    <table>
        <tr>
            <td class="third-width">Lazer/Férias  (  )</td>
            <td class="third-width">Negócios  (  )</td>
            <td class="third-width">Religião   (  )</td>
        </tr>
        <tr>
            <td class="third-width">Saúde  (  )</td>
            <td class="third-width">Compras   (  )</td>
            <td class="third-width">Outro   (  )</td>
        </tr>
    </table>

    <p class="section-title">Check-in e Check-out</p>
    <table>
        <tr>
            <td class="third-width"><strong>UH N°:</strong> {{ $reserva->quarto->numero ?? '—' }}</td>
            <td class="third-width"><strong>Check-in:</strong> {{ $reserva->checkin ? \Carbon\Carbon::parse($reserva->checkin->checkin_at)->format('d/m/Y H:i:s') : '—' }}</td>
            <td class="third-width"><strong>Check-out:</strong> {{ $reserva->data_checkout ? \Carbon\Carbon::parse($reserva->data_checkout)->format('d/m/Y H:i') : '—' }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>Número para contato em caso de emergência:</strong> {{ $reserva->contato_emergencia ?? '       ' }}</td>
        </tr>
    </table>

    <p class="section-title">Veículo / Vehicle</p>
    <table>
        <tr>
            <td class="third-width"><strong>Modelo / Model:</strong> {{ $reserva->veiculo_modelo ?? '       ' }}</td>
            <td class="third-width"><strong>Cor / Color:</strong> {{ $reserva->veiculo_cor ?? '       ' }}</td>
            <td class="third-width"><strong>Placa / Plate:</strong> {{ $reserva->veiculo_placa ?? '       ' }}</td>
        </tr>
    </table>

    @if($reserva->acompanhantes && $reserva->acompanhantes->count() > 0)
    <p class="section-title">Informações dos Acompanhantes / Companion Information</p>
    <table>
        <tr>
            <th style="width: 25%;">Nome / Name</th>
            <th style="width: 15%;">CPF</th>
            <th style="width: 15%;">Data Nasc. / Birth Date</th>
            <th style="width: 15%;">Tipo / Type</th>
            <th style="width: 15%;">E-mail</th>
            <th style="width: 15%;">Telefone / Phone</th>
        </tr>
        @foreach($reserva->acompanhantes as $acompanhante)
        <tr>
            <td>{{ $acompanhante->nome ?? '—' }}</td>
            <td>{{ $acompanhante->cpf ?? '—' }}</td>
            <td>{{ $acompanhante->data_nascimento ? \Carbon\Carbon::parse($acompanhante->data_nascimento)->format('d/m/Y') : '—' }}</td>
            <td>{{ $acompanhante->tipo ?? '—' }}</td>
            <td>{{ $acompanhante->email ?? '—' }}</td>
            <td>{{ $acompanhante->telefone ?? '—' }}</td>
        </tr>
        @endforeach
    </table>
    @endif
    
    <table>
        <p class="section-title">Meio de Transporte / Arriving By</p>

        <tr>
            <td>Avião  ( {{ $reserva->transporte == 'aviao' ? 'x' : ' ' }} )</td>
            <td>Automóvel  ( {{ $reserva->transporte == 'automovel' ? 'x' : ' ' }} )</td>
            <td>Ônibus  ( {{ $reserva->transporte == 'onibus' ? 'x' : ' ' }} )</td>
        </tr>
        <tr>
            <td>Moto  ( {{ $reserva->transporte == 'moto' ? 'x' : ' ' }} )</td>
            <td>Navio/Barco  ( {{ $reserva->transporte == 'navio' ? 'x' : ' ' }} )</td>
            <td>Trem  ( {{ $reserva->transporte == 'trem' ? 'x' : ' ' }} )</td>
        </tr>
    </table>
    <table>
        <tr class="full-width">
            <td>Termos LGPD</td>
        </tr>
    </table>

    <p></p>
    <p></p>

    <hr>
    <p class="text-center">Assinatura do Hóspede / Guest's Signature:</p>
</body>
</html>
