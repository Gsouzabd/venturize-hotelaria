@extends('layouts.admin.master')

@section('title', 'Entrar')

@section('body')
    <div class="authentication-wrapper authentication-1 px-4">
        <div class="authentication-inner py-4">

            <!-- Logo -->
            <div class="d-flex justify-content-center align-items-center mb-2">
                <div class="ui-w-240">
                    <div class="w-100 position-relative text-center" style="padding-bottom: 10.85715%">
                        <img src="{{ asset('assets/admin/images/aldeiadoscamaras.png') }}"
                             class="w-50 h-auto"
                             >
                    </div>
                </div>
            </div>
            <!-- / Logo -->

            <h6 class="text-center text-dark font-weight-bold mb-4">Sistema de Gestão</h6>
            <h5 class="text-center text-dark font-weight-bold mb-4">Pousada Aldeia dos Camarás</h5>

            <!-- Form -->
            <form action="{{ route('admin.login') }}" method="post" class="my-4">
                <div class="form-group">
                    <label class="form-label">Seu e-mail</label>
                    <input type="text" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Sua senha</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="d-flex justify-content-end m-0">
                    <button type="submit" class="btn w-100" style="background: #f05327; color:white; font-size:18px;">Entrar</button>
                </div>
            </form>
            <!-- / Form -->

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            $('input[name=email]').focus();

            appHelpers.doAjaxForm('form', {
                done: function (data) {
                    window.location = data.redirect_to;
                }
            });
        });
    </script>
@endpush
