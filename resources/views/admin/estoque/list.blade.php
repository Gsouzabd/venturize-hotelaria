@extends('layouts.admin.master')

@php 
use Carbon\Carbon;
use App\Models\Produto; 
@endphp

@section('title', 'Estoque')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        {{-- <x-admin.create-btn route="admin.estoque.create"/> --}}
    </x-admin.page-header>
@endsection

@section('content')
    <ul class="nav nav-tabs" id="estoqueTab" role="tablist">
        @foreach ($locaisEstoque as $index => $local)
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $local->id }}" data-toggle="tab" href="#content-{{ $local->id }}" role="tab" aria-controls="content-{{ $local->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">{{ $local->nome }}</a>
            </li>
        @endforeach
    </ul>
    <div class="tab-content" id="estoqueTabContent">
        @foreach ($locaisEstoque as $index => $local)
            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="content-{{ $local->id }}" role="tabpanel" aria-labelledby="tab-{{ $local->id }}">
                <x-admin.grid :pagination="$local->estoques">
                    <table class="table table-striped table-bordered table-hover card-table">
                        <thead>
                            <tr>
                                <th>ID Estoque</th>
                                <th>ID Produto</th>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($local->estoques as $estoque)
                                <tr>
                                    <td>{{ $estoque->id }}</td>
                                    <td>{{ $estoque->produto->id }}</td>
                                    <td>{{ $estoque->produto->descricao }}</td>
                                    <td>{{ $estoque->quantidade }}</td>
                                    <td>{{ $estoque->produto->unidade}} - {{\App\Models\Produto::UNIDADES[$estoque->produto->unidade] }}</td>
                                    <td>{{ Carbon::parse($estoque->created_at)->format('d-m-Y') }}</td>
                                    <td class="cell-nowrap">
                                        <x-admin.edit-btn route="admin.estoque.edit" :route-params="['local_estoque_id' => $local->id, 'id' => $estoque->id]" :label="html_entity_decode('<i class=\'fas fa-edit\'></i>')"/>
                                        <x-admin.delete-btn route="admin.estoque.destroy" :route-params="['id' => $estoque->id]" :label="html_entity_decode('<i class=\'fas fa-trash-alt\'></i>')"/>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Nenhum estoque encontrado para este local.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-admin.grid>
            </div>
        @endforeach
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center">
        {{ $estoques->links() }}
    </div>
@endsection