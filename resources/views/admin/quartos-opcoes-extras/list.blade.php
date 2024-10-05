@extends('layouts.admin.master')

@section('title', 'Lista de Opções Extras do Quarto')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.quartos-opcoes-extras.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <!-- Exibe mensagens de sucesso, se houver -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Exibe erros de validação, se houver -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-admin.grid :pagination="$opcoesExtras">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($opcoesExtras as $opcaoExtra)
                    <tr>
                        <td>{{ $opcaoExtra->id }}</td>
                        <td>{{ $opcaoExtra->nome }}</td>
                        <td>R$ {{ number_format($opcaoExtra->preco, 2, ',', '.') }}</td>
                        <td class="cell-nowrap">
                            <x-admin.edit-btn route="admin.quartos-opcoes-extras.edit" :route-params="['id' => $opcaoExtra->id]"/>
                            <x-admin.delete-btn route="admin.quartos-opcoes-extras.destroy" :route-params="['id' => $opcaoExtra->id]"/>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ config('app.messages.no_rows') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection