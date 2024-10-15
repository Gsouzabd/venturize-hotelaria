@extends('layouts.admin.master')

@section('title', 'Mesas')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.bar.mesas.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.bar.mesas.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Número"/>
            <x-admin.text name="numero" :value="$filters['numero']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Status"/>
            <x-admin.select name="status" :value="$filters['status']"
                :items="['Disponível' => 'Disponível', 'Ocupada' => 'Ocupada', 'Reservada' => 'Reservada']"
                selectedItem="{{ $filters['status'] }}"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$mesas">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Número</th>
                <th>Status</th>
                <th>Ativa</th>
                <th>Criado em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($mesas as $mesa)
                <tr>
                    <td>{{ $mesa->id }}</td>
                    <td>{{ $mesa->numero }}</td>
                    <td>{{ $mesa->status }}</td>
                    <td>{{ formata_bool($mesa->ativa) }}</td>
                    <td>{{ timestamp_br($mesa->created_at) }}</td>
                    <td>{{ timestamp_br($mesa->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.bar.mesas.edit" :route-params="['id' => $mesa->id]"/>
                        <x-admin.delete-btn route="admin.bar.mesas.destroy" :route-params="['id' => $mesa->id]"/>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ config('app.messages.no_rows') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection