@props([
    'name',
    'id' => null,
    'class' => '',
    'value' => '',
    'disabled' => false,
])

<div class="input-group">
    <input type="text"
           name="{{ $name }}"
           @if($id) id="{{ $id }}" @endif
           class="form-datepicker form-control date-mask{{ $class ? ' ' . $class : '' }}"
           value="{{ $value }}"{{ $disabled ? ' disabled' : '' }}>
    <div class="input-group-append">
        <span class="input-group-text">
            <i class="fas fa-calendar"></i>
        </span>
    </div>
</div>

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap-datepicker/bootstrap-datepicker.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/bootstrap-datepicker/bootstrap-datepicker.pt-BR.js') }}"></script>
    <script>
        $(function () {
            $('.form-datepicker').datepicker({
                language: "pt-BR",
                format: "dd/mm/yyyy",
                todayBtn: "linked",
                clearBtn: true,
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
@endpushonce
