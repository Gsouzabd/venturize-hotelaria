@extends('layouts.admin.master')

@section('title', 'Pedidos')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        {{-- <x-admin.create-btn route="admin.bar.pedidos.create"/> --}}
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.bar.pedidos.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Número"/>
            <x-admin.text name="numero" :value="$filters['numero']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Status"/>
            <x-admin.text name="status" :value="$filters['status']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$pedidos">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Mesa</th>
                <th>Reserva</th>
                <th>Cliente Responsável</th>
                <th>Status</th>
                <th>Aberto em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($pedidos as $pedido)
                <tr>
                    <td>{{ $pedido->id }}</td>
                    <td>{{ $pedido->mesa->numero }}</td>
                    <td>{{ $pedido->reserva->id }}</td>
                    <td>{{ $pedido->cliente->nome }}</td>
                    <td>{{ $pedido->status }}</td>
                    <td>{{ timestamp_br($pedido->created_at) }}</td>
                    <td>{{ timestamp_br($pedido->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.bar.pedidos.edit" :route-params="['id' => $pedido->id]"/>
                        <x-admin.delete-btn route="admin.bar.pedidos.destroy" :route-params="['id' => $pedido->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection