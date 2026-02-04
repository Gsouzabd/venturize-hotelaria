@extends('layouts.admin.master')

@section('title', 'Impressoras')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.impressoras.create"/>
    </x-admin.page-header>
@endsection

@section('content')
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

