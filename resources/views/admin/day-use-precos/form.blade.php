@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Novo') . ' Plano de Day Use')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ $edit ? 'Editar Plano de Day Use' : 'Cadastrar Plano de Day Use' }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.day-use-precos.save') }}" method="POST">
                @csrf
                @if($edit)
                    <input type="hidden" name="id" value="{{ $plano->id }}">
                @endif

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Data Início</label>
                            <input type="date" name="data_inicio" class="form-control"
                                   value="{{ old('data_inicio', optional($plano->data_inicio)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Data Fim</label>
                            <input type="date" name="data_fim" class="form-control"
                                   value="{{ old('data_fim', optional($plano->data_fim)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_default" name="is_default"
                                   {{ old('is_default', $plano->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">Plano Padrão</label>
                        </div>
                    </div>
                </div>

                <hr>

                <h5>Preços por Dia da Semana</h5>
                <div class="row">
                    @php
                        $dias = [
                            'preco_segunda' => 'Segunda',
                            'preco_terca' => 'Terça',
                            'preco_quarta' => 'Quarta',
                            'preco_quinta' => 'Quinta',
                            'preco_sexta' => 'Sexta',
                            'preco_sabado' => 'Sábado',
                            'preco_domingo' => 'Domingo',
                        ];
                    @endphp
                    @foreach($dias as $campo => $label)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ $label }}</label>
                                <input type="number" step="0.01" min="0" name="{{ $campo }}" class="form-control"
                                       value="{{ old($campo, $plano->$campo) }}">
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Preço Café (Seg–Sex) - por pessoa</label>
                            <input type="number" step="0.01" min="0" name="preco_cafe_semana" class="form-control"
                                   value="{{ old('preco_cafe_semana', $plano->preco_cafe_semana ?? $plano->preco_cafe) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Preço Café (Sáb–Dom) - por pessoa</label>
                            <input type="number" step="0.01" min="0" name="preco_cafe_fim_semana" class="form-control"
                                   value="{{ old('preco_cafe_fim_semana', $plano->preco_cafe_fim_semana ?? $plano->preco_cafe) }}">
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <a href="{{ route('admin.day-use-precos.index') }}" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

