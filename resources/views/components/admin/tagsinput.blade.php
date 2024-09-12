@props([
    'name',
    'class' => '',
    'value' => '',
    'disabled' => false,
])

<input type="text"
       name="{{ $name }}"
       class="form-tagsinput form-control{{ $class ? ' ' . $class : '' }}"
       value="{{ $value }}"{{ $disabled ? ' disabled' : '' }}>

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
    <script>
        $(function () {
            $(".form-tagsinput").tagsinput();
        });
    </script>
@endpushonce
