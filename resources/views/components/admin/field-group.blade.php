@props(['class' => ''])

<div class="form-row{{ $class ? ' ' . $class : '' }}"{!! $attributes ? ' ' . $attributes : '' !!}>
    {{ $slot }}
</div>
