@extends('layouts.admin.master')

@section('title', 'Importar usuários')

@section('content-header')
    <x-admin.page-header :title="view()->getSection('title')"/>
@endsection

@section('content')
    <x-admin.form save-route="admin.importar-usuarios.store"
                  back-route="admin.usuarios.index"
                  files-enctype>
        <div class="card">
            <div class="card-body pb-2">
                <x-admin.field>
                    <x-admin.label label="Arquivo" required/>
                    <x-admin.filepond name="arquivo"
                                      :max-file-size="config('app.csv_max_size') . 'MB'"
                                      :accepted-file-types="config('app.valid_csv_mimetypes')"
                                      store-as-file/>
                    <x-admin.help-text>
                        Somente arquivos {{ implode(', ', config('app.valid_csv_extensions')) }}. Tamanho máximo
                        de {{ config('app.csv_max_size') . 'MB' }}. O conteúdo tem de ser conforme abaixo:
                    </x-admin.help-text>

                    <x-admin.callout type="secondary" class="mt-1">
                        Nome;E-mail;Plano<br>
                        Beltrano da Silva;beltrano@teste.com.br;Ouro<br>
                        Ciclano da Silva;ciclano@teste.com.br;Prata<br>
                        Fulano da Silva;fulano@teste.com.br;Bronze
                    </x-admin.callout>
                </x-admin.field>
            </div>
        </div>
    </x-admin.form>
@endsection
