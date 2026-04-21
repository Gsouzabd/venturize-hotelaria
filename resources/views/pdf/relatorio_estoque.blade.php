<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de estoque</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 12px; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #333; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px 3px; text-align: left; }
        th { background: #eee; font-weight: bold; }
        td.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Relatório de estoque de produtos</h1>
    <div class="meta">
        Gerado em: {{ $geradoEm }}<br>
        @if(!empty($filters['local_estoque_id']) && $nomeLocalFiltro)
            Local filtrado: {{ $nomeLocalFiltro }}<br>
        @endif
        Produtos: {{ ($filters['somente_ativos'] ?? '1') === '1' ? 'somente ativos' : 'todos' }}
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Cód. int.</th>
                <th>Categoria</th>
                <th>Local</th>
                <th>Qtd</th>
                <th>Un.</th>
                <th>Mín.</th>
                <th>Máx.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estoques as $row)
                @php $p = $row->produto; @endphp
                @if($p)
                    <tr>
                        <td>{{ $row->id }}</td>
                        <td>{{ $p->descricao }}</td>
                        <td>{{ $p->codigo_interno ?? '—' }}</td>
                        <td>{{ $p->categoria->nome ?? '—' }}</td>
                        <td>{{ $row->localEstoque->nome ?? '—' }}</td>
                        <td class="num">{{ $row->quantidade }}</td>
                        <td>{{ $unidades[$p->unidade] ?? $p->unidade }}</td>
                        <td class="num">{{ $p->estoque_minimo ?? '—' }}</td>
                        <td class="num">{{ $p->estoque_maximo ?? '—' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</body>
</html>
