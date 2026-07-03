@extends('layouts.admin.master')

@section('title', 'Movimentações de Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.movimentacoes-estoque.create" label="Inserir Entrada/Saída"/>
        <x-admin.create-btn route="admin.movimentacoes-estoque.transf" label="Realizar Transferência"/>    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.ajuda titulo="Como funcionam as movimentações?">
        <ul class="mb-0 pl-3">
            <li><strong>Entrada:</strong> aumenta o saldo de um produto em um sub-local. Use quando chegar compra ou mercadoria ("Inserir Entrada/Saída", tipo Entrada).</li>
            <li><strong>Saída:</strong> diminui o saldo. Use para consumo, perda ou quebra — informe a justificativa.</li>
            <li><strong>Transferência:</strong> move quantidade de um sub-local para outro (ex.: Almoxarifado › Limpeza para Lavanderia › Descartável) sem alterar o total geral.</li>
            <li><strong>Onde ver o resultado:</strong> o saldo atualizado aparece na tela <a href="{{ route('admin.estoque.index') }}">Estoque</a> e no <a href="{{ route('admin.relatorios.estoque') }}">Relatório de Estoque</a>. As abas abaixo listam o histórico de cada local.</li>
        </ul>
    </x-admin.ajuda>

    <ul class="nav nav-tabs" id="movimentacoesTab" role="tablist">
        @foreach ($locaisEstoque as $index => $local)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $local->id }}" data-toggle="tab" href="#content-{{ $local->id }}" role="tab" aria-controls="content-{{ $local->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ $local->nome }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="movimentacoesTabContent">
        @foreach ($locaisEstoque as $index => $local)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $local->id }}" role="tabpanel" aria-labelledby="tab-{{ $local->id }}">
                <x-admin.grid>
                    <table class="table table-striped table-bordered table-hover card-table">
                        <thead>
                            <tr>
                                <th>ID Movimentação</th>
                                <th>Produto</th>
                                <th>Local</th>
                                <th>Quantidade</th>
                                <th>Tipo</th>
                                <th>Justificativa</th>
                                <th>Usuário</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Movimentações do local pai e de todos os sub-locais, sem duplicar
                                $grupo = collect([$local])->merge($local->children);
                                $idsGrupo = $grupo->pluck('id');
                                $movimentacoes = $grupo
                                    ->flatMap(fn ($l) => $l->movimentacoesOrigem->merge($l->movimentacoesDestino))
                                    ->unique('id')
                                    ->sortByDesc('data_movimentacao');
                            @endphp
                            @forelse ($movimentacoes as $movimentacao)
                                <tr>
                                    <td>{{ $movimentacao->id }}</td>
                                    <td>{{ $movimentacao->produto->descricao }}</td>
                                    <td>
                                        @if($movimentacao->tipo == 'transferencia')
                                            {{ $movimentacao->localOrigem->nome ?? '—' }} → {{ $movimentacao->localDestino->nome ?? '—' }}
                                        @else
                                            {{ $movimentacao->localDestino->nome ?? $movimentacao->localOrigem->nome ?? '—' }}
                                        @endif
                                    </td>
                                    <td>{{ $movimentacao->quantidade }}</td>
                                    <td>
                                        {{ ucfirst($movimentacao->tipo) }}
                                        @if($movimentacao->tipo == 'transferencia' && !($idsGrupo->contains($movimentacao->local_estoque_origem_id) && $idsGrupo->contains($movimentacao->local_estoque_destino_id)))
                                            ( {{ $idsGrupo->contains($movimentacao->local_estoque_destino_id) ? 'Entrada' : 'Saída' }} )
                                        @endif
                                    </td>
                                    <td>{{ $movimentacao->justificativa }}</td>
                                    <td>{{ $movimentacao->usuario->nome }}</td>
                                    <td>{{ Carbon\Carbon::parse($movimentacao->data_movimentacao)->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <!-- Add action buttons here -->
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Nenhuma movimentação encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.grid>
            </div>
        @endforeach
    </div>
@endsection
