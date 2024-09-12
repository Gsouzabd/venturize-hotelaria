@extends('layouts.admin.master')

@section('title', 'Quartos')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.quartos.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.quartos.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Andar"/>
            <x-admin.text name="andar" :value="$filters['andar']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Número"/>
            <x-admin.text name="numero" :value="$filters['numero']"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$quartos">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Andar</th>
                <th>Número</th>
                <th>Classificação</th>
                <th>Inativo</th>
                <th>Criado em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($quartos as $quarto)
                <tr>
                    <td>{{ $quarto->id }}</td>
                    <td>{{ $quarto->andar }}</td>
                    <td>{{ $quarto->numero }}</td>
                    <td>{{ $quarto->classificacao }}</td>
                    <td>{{ formata_bool($quarto->inativo) }}</td>
                    <td>{{ timestamp_br($quarto->created_at) }}</td>
                    <td>{{ timestamp_br($quarto->updated_at) }}</td>
                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.quartos.edit" :route-params="['id' => $quarto->id]"/>
                        <x-admin.delete-btn route="admin.quartos.destroy" :route-params="['id' => $quarto->id]"/>
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
@endsection
