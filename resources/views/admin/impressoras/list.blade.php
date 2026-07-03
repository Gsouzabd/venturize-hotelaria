@extends('layouts.admin.master')

@section('title', 'Impressoras')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.impressoras.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.ajuda titulo="Como funciona a impressão e como instalar o Agente?">
        <p class="mb-2"><strong>Arquitetura:</strong> quando um pedido do bar/cozinha é criado, ele entra na fila de impressão com status <em>pendente</em>. A impressão acontece por duas vias:</p>
        <ul class="pl-3">
            <li><strong>Direta (servidor):</strong> o sistema envia comandos ESC/POS direto para a impressora térmica pelo IP e porta (padrão 9100) cadastrados nesta tela.</li>
            <li><strong>PrintingAgent (computador local):</strong> um programa instalado no PC da recepção/cozinha consulta a fila da API (<code>/api/print/pedidos-pendentes</code>) a cada 5 segundos e imprime nas impressoras da rede local. O ciclo de cada impressão é: <em>pendente → processando → sucesso</em> ou <em>erro</em> (com nova tentativa registrada).</li>
        </ul>
        <p class="mb-2"><strong>Instalação do Agente (arquivo Agentimpressao.zip):</strong></p>
        <ol class="pl-3 mb-2">
            <li>Extraia o <code>Agentimpressao.zip</code> em uma pasta fixa do computador (ex.: <code>C:\PrintingAgent</code>) — não use pasta temporária.</li>
            <li>Edite o arquivo <code>.env</code> da pasta: confira o <code>API_BASE_URL</code> apontando para <code>https://venturize.com.br/api/print</code>.</li>
            <li>Execute o <code>setup-printers.exe</code> para configurar os IPs das impressoras (não preencha os IPs manualmente no .env).</li>
            <li>Execute o <code>install-service.bat</code> <strong>como Administrador</strong> — isso cria a tarefa que inicia o agente junto com o Windows.</li>
            <li>Use o <code>gerenciar-servico.bat</code> para iniciar/parar o agente. Os registros ficam na pasta <code>logs/</code>.</li>
        </ol>
        <p class="mb-0"><strong>Nesta tela</strong> você cadastra as impressoras (nome, IP e porta) que o sistema usa; a "Ordem" define a sequência de exibição e o "Status" ativa/desativa a impressora sem excluí-la. O IP precisa ser fixo na rede local (reserve no roteador).</p>
    </x-admin.ajuda>

    <x-admin.filters route="admin.impressoras.index">
        <x-admin.filter cols="3">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>
        <x-admin.filter cols="3">
            <x-admin.label label="Status"/>
            <x-admin.select name="ativo" :value="$filters['ativo']">
                <option value="">Todos</option>
                <option value="1" {{ $filters['ativo'] === '1' ? 'selected' : '' }}>Ativas</option>
                <option value="0" {{ $filters['ativo'] === '0' ? 'selected' : '' }}>Inativas</option>
            </x-admin.select>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$impressoras">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>IP</th>
                <th>Porta</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Ordem</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($impressoras as $impressora)
                <tr>
                    <td>{{ $impressora->id }}</td>
                    <td>{{ $impressora->nome }}</td>
                    <td>{{ $impressora->ip }}</td>
                    <td>{{ $impressora->porta }}</td>
                    <td>
                        <span class="badge badge-info">
                            {{ ucfirst($impressora->tipo) }}
                        </span>
                    </td>
                    <td>
                        @if($impressora->ativo)
                            <span class="badge badge-success">Ativa</span>
                        @else
                            <span class="badge badge-secondary">Inativa</span>
                        @endif
                    </td>
                    <td>{{ $impressora->ordem }}</td>
                    <td class="cell-nowrap">
                        <button 
                            class="btn btn-sm btn-info testar-impressora" 
                            data-id="{{ $impressora->id }}"
                            data-nome="{{ $impressora->nome }}"
                            title="Testar Conectividade">
                            <i class="fas fa-network-wired"></i>
                        </button>
                        <x-admin.edit-btn route="admin.impressoras.edit" :route-params="['id' => $impressora->id]"/>
                        <x-admin.delete-btn route="admin.impressoras.destroy" :route-params="['id' => $impressora->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.testar-impressora').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nome = this.dataset.nome;
                const btnOriginal = this;
                const iconOriginal = btnOriginal.querySelector('i').className;
                
                // Desabilitar botão e mostrar loading
                btnOriginal.disabled = true;
                btnOriginal.querySelector('i').className = 'fas fa-spinner fa-spin';
                
                fetch(`/admin/impressoras/${id}/testar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btnOriginal.disabled = false;
                    btnOriginal.querySelector('i').className = iconOriginal;
                    
                    if (data.success) {
                        alert(`✅ ${nome}\n\n${data.message}`);
                    } else {
                        alert(`❌ ${nome}\n\n${data.message}`);
                    }
                })
                .catch(err => {
                    btnOriginal.disabled = false;
                    btnOriginal.querySelector('i').className = iconOriginal;
                    alert(`Erro ao testar impressora: ${err.message}`);
                });
            });
        });
    });
    </script>
@endsection

