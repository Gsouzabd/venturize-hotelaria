@extends('layouts.admin.master')

@section('title', 'Usuários')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')">
        <x-admin.action-btn route="admin.importar-usuarios.index" title="Importar usuários"/>
        <x-admin.create-btn route="admin.usuarios.create"/>
    </x-admin.page-header>
@endsection

@section('content')
    <x-admin.filters route="admin.usuarios.index">
        <x-admin.filter cols="2">
            <x-admin.label label="Nome"/>
            <x-admin.text name="nome" :value="$filters['nome']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="E-mail"/>
            <x-admin.text name="email" :value="$filters['email']"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Tipo"/>
            <x-admin.select name="tipo"
                            :items="config('app.enums.tipos_usuario')"
                            :selected-item="$filters['tipo']"
                            placeholder="Todos"/>
        </x-admin.filter>

        <x-admin.filter cols="2">
            <x-admin.label label="Ativo"/>
            <x-admin.select name="fl_ativo"
                            :items="['1' => 'Sim', '0' => 'Não']"
                            :selected-item="$filters['fl_ativo']"
                            placeholder="Todos"/>
        </x-admin.filter>
    </x-admin.filters>

    <x-admin.grid :pagination="$usuarios">
        <table class="table table-striped table-bordered table-hover card-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Tipo</th>
                <th>Ativo</th>
                <th>Grupo</th> <!-- Nova coluna para o grupo -->
                <th>Criado em</th>
                <th>Modificado em</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->id }}</td>
                    <td>{{ $usuario->nome }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->tipo }}</td>
                    <td>{{ formata_bool($usuario->fl_ativo) }}</td>
                    <td>{{ $usuario->grupoUsuario->nome ?? 'Sem grupo' }}</td> <!-- Exibe o nome do grupo -->
                    <td>{{ timestamp_br($usuario->created_at) }}</td>
                    <td>{{ timestamp_br($usuario->updated_at) }}</td>
                    <td class="cell-nowrap">
                        {{-- <button type="button"
                                class="btn btn-xs btn-outline-secondary btn-resend-password"
                                data-route="{{ route('admin.usuarios.resend-password', ['id' => $usuario->id]) }}">
                            Enviar nova senha
                        </button> --}}
                        <x-admin.edit-btn route="admin.usuarios.edit" :route-params="['id' => $usuario->id]"/>
                        <x-admin.delete-btn route="admin.usuarios.destroy" :route-params="['id' => $usuario->id]"/>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ config('app.messages.no_rows') }}</td> <!-- Atualizado o colspan -->
                </tr>
            @endforelse
            </tbody>
        </table>
    </x-admin.grid>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('.btn-resend-password').click(function () {
                const self = $(this);
                const route = self.attr('data-route');

                Swal.fire({
                    title: "Deseja realmente enviar uma nova senha?",
                    text: "Essa ação é irreversível!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Enviar definitivamente"
                }).then(function (result) {
                    if (result.value) {
                        appHelpers.showPageLoader();

                        $.ajax({
                            method: "GET",
                            url: route
                        }).done(function (data) {
                            appHelpers.hidePageLoader();

                            Swal.fire({
                                title: "Confirmado!",
                                text: data.message,
                                icon: "success",
                                confirmButtonColor: "#8cd4f5",
                                confirmButtonText: "Fechar"
                            });
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                            appHelpers.hidePageLoader();

                            const response = JSON.parse(jqXHR.responseText);
                            const error = response.message || 'Ocorreu um erro desconhecido.';

                            Swal.fire("Ops!", error, "error");
                        });
                    }
                });
            });
        });
    </script>
@endpush
