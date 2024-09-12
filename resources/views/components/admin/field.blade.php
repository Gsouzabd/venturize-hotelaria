@props([
    'cols' => null,
    'class' => '',
])

<div class="form-group{{ $cols ? ' col-xl-' . $cols : '' }} mb-4{{ $class ? ' ' . $class : '' }}"{!! $attributes ? ' ' . $attributes : '' !!}>
    {{ $slot }}
</div>
