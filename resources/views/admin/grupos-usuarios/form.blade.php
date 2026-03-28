@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' Grupo de Usuário')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.form save-route="admin.grupos-usuarios.save"
                  back-route="admin.grupos-usuarios.index"
                  :is-edit="$edit">
        <div class="card">
            <div class="card-body pb-2">
                @if($edit)
                    <x-admin.field-group>
                        <x-admin.field cols="3">
                            <x-admin.label label="ID"/>
                            <x-admin.text name="id" :value="old('id', $grupo->id)" readonly/>
                        </x-admin.field>
                    </x-admin.field-group>
                @endif

                <x-admin.field>
                    <x-admin.label label="Nome" required/>
                    <x-admin.text name="nome" :value="old('nome', $grupo->nome)"/>
                </x-admin.field>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Permissões</h5>
            </div>
            <div class="card-body">
                @php
                    $permissoesAgrupadas = [];
                    $permissoesConfig = config('app.enums.permissoes_plano', []);
                    foreach ($permissoes as $permissao) {
                        $label = $permissoesConfig[$permissao->nome] ?? $permissao->nome;
                        // Agrupar por categoria (parte após visualizar_/gerenciar_)
                        $parts = explode('_', $permissao->nome, 2);
                        $categoria = isset($parts[1]) ? ucfirst(str_replace('_', ' ', $parts[1])) : 'Outros';
                        $permissoesAgrupadas[$categoria][] = $permissao;
                    }
                    ksort($permissoesAgrupadas);
                    $oldPermissoes = old('permissoes', $permissoesSelecionadas);
                @endphp

                <div class="row">
                    @foreach($permissoesAgrupadas as $categoria => $perms)
                        <div class="col-md-4 mb-3">
                            <h6 class="font-weight-bold text-primary">{{ $categoria }}</h6>
                            @foreach($perms as $permissao)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="permissao_{{ $permissao->id }}"
                                           name="permissoes[]"
                                           value="{{ $permissao->id }}"
                                           {{ in_array($permissao->id, $oldPermissoes) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="permissao_{{ $permissao->id }}">
                                        {{ $permissoesConfig[$permissao->nome] ?? $permissao->nome }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-select-all">Selecionar Todas</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">Desmarcar Todas</button>
                </div>
            </div>
        </div>
    </x-admin.form>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('#btn-select-all').click(function () {
                $('input[name="permissoes[]"]').prop('checked', true);
            });
            $('#btn-deselect-all').click(function () {
                $('input[name="permissoes[]"]').prop('checked', false);
            });
        });
    </script>
@endpush
