@extends('layouts.admin.master')

@section('title', 'Entrar')

@section('body')
    <div class="authentication-wrapper authentication-1 px-4">
        <div class="authentication-inner py-4">

            <!-- Logo -->
            <div class="d-flex justify-content-center align-items-center mb-2">
                <div class="ui-w-240">
                    <div class="w-100 position-relative text-center" style="padding-bottom: 20.85715%">
                        <img src="{{ asset('assets/admin/images/aldeiadoscamaras.png') }}"
                             class="w-50 h-auto"
                             >
                    </div>
                </div>
            </div>
            <!-- / Logo -->

            {{-- <h5 class="text-center text-muted font-weight-normal mb-4">{{ config('app.name') }}</h5> --}}

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
                    <button type="submit" class="btn btn-primary">Entrar</button>
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
