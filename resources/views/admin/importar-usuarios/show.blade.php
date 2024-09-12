@extends('layouts.admin.master')

@section('title', 'Importação de usuários finalizada')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <div class="card mb-0 border-bottom-0 rounded-bottom-0">
        <div class="card-header font-weight-bold text-uppercase">
            Registros do arquivo
        </div>

        <div class="table-responsive">
            <table class="table table-hover card-table">
                <thead class="thead-light">
                <tr>
                    <th>Registro</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Plano</th>
                    <th>Situação</th>
                    <th>Mensagem</th>
                </tr>
                </thead>
                <tbody>
                @foreach($registros as $registro)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $registro['nome'] }}</td>
                        <td>{{ $registro['email'] }}</td>
                        <td>{{ $registro['plano'] }}</td>
                        <td>{{ $registro['situacao'] }}</td>
                        <td>{{ $registro['mensagem'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ count($registros) . ' ' . (count($registros) > 1 ? 'registros encontrados.' : 'registro encontrado.') }}
        </div>
    </div>
@endsection
