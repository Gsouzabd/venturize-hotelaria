@extends('layouts.admin.master')

@section('title', ($edit ? 'Editando' : 'Inserindo') . ' usuário')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.form save-route="admin.usuarios.save"
                  back-route="admin.usuarios.index"
                  :is-edit="$edit">
        <div class="card">
            <div class="card-body pb-2">
                @if($edit)
                    <x-admin.field-group>
                        <x-admin.field cols="3">
                            <x-admin.label label="ID"/>
                            <x-admin.text name="id" :value="old('id', $usuario->id)" readonly/>
                        </x-admin.field>
                    </x-admin.field-group>
                @endif

                <x-admin.field>
                    <x-admin.label label="Nome" required/>
                    <x-admin.text name="nome" :value="old('nome', $usuario->nome)"/>
                </x-admin.field>

                <x-admin.field>
                    <x-admin.label label="E-mail" required/>
                    <x-admin.text name="email" :value="old('email', $usuario->email)"/>
                </x-admin.field>

                <x-admin.field-group>
                    <x-admin.field cols="3">
                        <x-admin.label label="Tipo" required/>
                        <x-admin.select name="tipo"
                                        :items="config('app.enums.tipos_usuario')"
                                        :selected-item="old('tipo', $usuario->tipo)"/>
                    </x-admin.field>

                    <!-- Campo de Seleção do Grupo de Usuário -->
                    <x-admin.field cols="3">
                        <x-admin.label label="Grupo de Usuário" required/>
                        <x-admin.select name="grupo_usuario_id"
                                        :items="$gruposUsuarios"
                                        :selected-item="old('grupo_usuario_id', $usuario->grupo_usuario_id)"/>
                    </x-admin.field>
                </x-admin.field-group>

                <x-admin.field>
                    <x-admin.label label="Ativo"/>
                    <x-admin.switcher name="fl_ativo"
                                      :checked="$errors->any() ? old('fl_ativo') : $usuario->fl_ativo"/>
                </x-admin.field>

                <x-admin.field-group>
                    <x-admin.field cols="6">
                        <x-admin.label label="Senha" required/>
                        <x-admin.password name="senha"/>
                    </x-admin.field>

                    <x-admin.field cols="6">
                        <x-admin.label label="Confirmar senha" required/>
                        <x-admin.password name="senha_confirmation"/>
                    </x-admin.field>
                </x-admin.field-group>
            </div>
        </div>
    </x-admin.form>
@endsection
