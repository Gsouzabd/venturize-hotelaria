@extends('layouts.admin.master')
@php 
use Carbon\Carbon; 
use App\Models\Reserva;

@endphp

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

        <x-admin.filter cols="2">
            <x-admin.label label="Tipo de Reserva"/>
            <select name="tipo_reserva" class="form-control">
                <option value="">Todas</option>
                <option value="INDIVIDUAL" {{ ($filters['tipo_reserva'] ?? '') === 'INDIVIDUAL' ? 'selected' : '' }}>Individual</option>
                <option value="GRUPO" {{ ($filters['tipo_reserva'] ?? '') === 'GRUPO' ? 'selected' : '' }}>Grupo</option>
                <option value="DAY_USE" {{ ($filters['tipo_reserva'] ?? '') === 'DAY_USE' ? 'selected' : '' }}>Day Use</option>
            </select>
        </x-admin.filter>
        
        <!-- Filtro de Período -->
        <x-admin.filter cols="4">
            <x-admin.label label="Período"/>
            <div class="d-flex">
                <x-admin.datepicker name="data_checkin" :value="$filters['data_checkin']" placeholder="Data Check-in"/>
                <span class="mx-2">até</span>
                <x-admin.datepicker name="data_checkout" :value="$filters['data_checkout']" placeholder="Data Check-out"/>
            </div>
        </x-admin.filter>
    
        <!-- Filtro de Data de Criação -->
        {{-- <x-admin.filter cols="2">
            <x-admin.label label="Data de Criação"/>
            <x-admin.datepicker  name="created_at" :value="$filters['created_at']"/>
        </x-admin.filter> --}}
    
        <!-- Filtro de Operador -->
        <x-admin.filter cols="2">
            <x-admin.label label="Operador"/>
            <x-admin.select name="operador_id"
                            :items="$operadores"
                            :selected-item="$filters['operador_id']"
                            placeholder="Todos"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$reservas">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tipo</th>

                @if(\Illuminate\Support\Facades\Route::currentRouteName() !== 'admin.reservas.day-use')
                    <th>Cliente Solicitante</th>
                @endif                
                @if(\Illuminate\Support\Facades\Route::currentRouteName() === 'admin.reservas.day-use')
                    <th>Adultos</th>
                    <th>Crianças</th>
                @else
                    <th>Quarto</th>
                @endif
                <th>Cliente Responsável</th>
                <th>Situação</th>
                <th>Check-in</th> <!-- Novo campo -->
                @if(\Illuminate\Support\Facades\Route::currentRouteName() !== 'admin.reservas.day-use')
                    <th>Check-out</th> <!-- Novo campo -->
                @endif
                <th>Valor Total</th>
                <th>Criado em</th>
                <th>Operador</th>

                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($reservas as $reserva)
                <tr>
                    <td>{{ $reserva->id }}</td>
                    <td width="110">
                        @php
                            $tipo = $reserva->tipo_reserva;
                            $labelTipo = $tipo && isset(Reserva::TIPOSRESERVA[$tipo])
                                ? Reserva::TIPOSRESERVA[$tipo]
                                : ($tipo ?? '—');
                        @endphp
                        @if($tipo)
                            <span class="badge {{ $tipo === 'DAY_USE' ? 'badge-warning' : 'badge-secondary' }}">
                                {{ $labelTipo }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                    @if(\Illuminate\Support\Facades\Route::currentRouteName() === 'admin.reservas.day-use')
                        <td>{{ $reserva->adultos }}</td>
                        <td>{{ $reserva->criancas_ate_7 + $reserva->criancas_mais_7 }}</td>

                    @else
                    <td>{{ $reserva->clienteSolicitante->nome}}</td>

                        <td>{{ optional($reserva->quarto)->numero ?? '—' }}</td>
                    @endif
                    <td>{{ $reserva->clienteResponsavel ? $reserva->clienteResponsavel->nome : "GR: ".$reserva->clienteSolicitante->nome  }}</td>
                    <td width="100">
                        <span class="status-reserva" style="background: {{Reserva::SITUACOESRESERVA[$reserva->situacao_reserva]['background']}};">
                            {{ $reserva->situacao_reserva }}
                        </span>
                    </td>
                    <td width="100">{{ Carbon::parse($reserva->data_checkin)->format('d-m-Y') }}</td> <!-- Novo campo -->
                    @if(\Illuminate\Support\Facades\Route::currentRouteName() !== 'admin.reservas.day-use')
                        <td width="100">{{ Carbon::parse($reserva->data_checkout)->format('d-m-Y') }}</td> <!-- Novo campo -->
                    @endif
                    <td width="100">R$ {{$reserva->total  ?? '' }}</R$>
                    <td width="100">{{ timestamp_br($reserva->created_at) }}</td>
                    <td>{{ $reserva->operador->nome }}</td>

                    <td class="cell-nowrap">
                        <x-admin.edit-btn route="admin.reservas.edit" :route-params="['id' => $reserva->id]" :label="html_entity_decode('<i class=\'fas fa-edit\'></i>')"/>
                        <x-admin.delete-btn route="admin.reservas.destroy" :route-params="['id' => $reserva->id]" :label="html_entity_decode('<i class=\'fas fa-trash-alt\'></i>')"/>
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
