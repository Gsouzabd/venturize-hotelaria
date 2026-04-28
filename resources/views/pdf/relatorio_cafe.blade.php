<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Listagem de café</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 12px; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #333; margin-bottom: 12px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 6px; text-align: left; }
        th { background: #eee; font-weight: bold; }
        .col-check { width: 28px; text-align: center; }
        .col-qtd { width: 50px; text-align: center; }
        .totais { font-weight: bold; text-transform: uppercase; margin-top: 12px; font-size: 11px; border: 1px solid #333; padding: 6px 10px; background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Listagem de café</h1>
    <div class="meta">
        Data de referência (café ~9h): <strong>{{ $dataReferencia }}</strong><br>
        Critério: check-in do hotel às 15h — inclui reservas HOSPEDADO com data de check-in anterior ao dia
        e data de check-out no dia ou depois.
    </div>
    <table>
        <thead>
            <tr>
                <th>Quarto</th>
                <th>Tipo</th>
                <th>Adulto/Criança</th>
                <th class="col-qtd">Qtd./Quarto</th>
                <th>Nome</th>
                <th>CPF</th>
                <th class="col-check">✓</th>
            </tr>
        </thead>
        <tbody>
            @forelse($linhas as $linha)
                <tr>
                    <td>{{ $linha['quarto'] }}</td>
                    <td>{{ $linha['tipo'] }}</td>
                    <td>{{ $linha['tipo_pessoa'] }}</td>
                    <td class="col-qtd">{{ $linha['qtd_pessoas_quarto'] }}</td>
                    <td>{{ $linha['nome'] }}</td>
                    <td>{{ $linha['cpf'] !== '' ? $linha['cpf'] : '—' }}</td>
                    <td class="col-check"></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhum hóspede nesta data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($totalGeral > 0)
    <div class="totais">
        TOTAL ADULTOS: {{ $totalAdultos }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        TOTAL CRIANÇAS: {{ $totalCriancas }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        TOTAL GERAL: {{ $totalGeral }}
    </div>
    @endif
</body>
</html>
