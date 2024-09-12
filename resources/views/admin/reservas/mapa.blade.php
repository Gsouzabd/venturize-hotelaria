@extends('layouts.admin.master')

@section('title', 'Mapa de Ocupação')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.create-btn route="admin.reservas.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.ocupacao-mapa 
        :reservas="$reservas" 
        :quartos="$quartos" 
        :dataInicial="$dataInicial" 
        :intervaloDias="$intervaloDias" 
        action="{{ route('admin.reservas.mapa') }}" />
@endsection
