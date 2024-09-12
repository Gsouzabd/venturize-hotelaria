@props([
    'cols' => '2',
    'class' => '',
])

<div class="col-xl-{{ $cols }} mb-4{{ $class ? ' ' . $class : '' }}"{!! $attributes ? ' ' . $attributes : '' !!}>
    {{ $slot }}
</div>
