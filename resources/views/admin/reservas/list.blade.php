@extends('layouts.admin.master')

@section('title', 'Reservas')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.reservas.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.reservas.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Cliente"/>
            <x-admin.text name="cliente" :value="$filters['cliente_id']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Quarto"/>
            <x-admin.select name="quarto_id"
                            :items="$quartos"
                            :selected-item="$filters['quarto_id']"
                            placeholder="Todos"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$reservas">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Quarto</th>
                <th>Operador</th>
                <th>Situação</th>
                <th>Previsão Chegada</th>
                <th>Previsão Saída</th>
                <th>Check-in</th> <!-- Novo campo -->
                <th>Check-out</th> <!-- Novo campo -->
                <th>Criado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($reservas as $reserva)
                <tr>
                    <td>{{ $reserva->id }}</td>
                    <td>{{ $reserva->cliente->nome }}</td>
                    <td>{{ $reserva->quarto->numero }}</td>
                    <td>{{ $reserva->operador->nome }}</td>
                    <td>{{ $reserva->situacao_reserva }}</td>
                    <td>{{ $reserva->previsao_chegada }}</td>
                    <td>{{ $reserva->previsao_saida }}</td>
                    <td>{{ $reserva->data_checkin }}</td> <!-- Novo campo -->
                    <td>{{ $reserva->data_checkout }}</td> <!-- Novo campo -->
                    <td>{{ timestamp_br($reserva->created_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.reservas.edit" :route-params="['id' => $reserva->id]"/>
                        <x-admin.delete-btn route="admin.reservas.destroy" :route-params="['id' => $reserva->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection
