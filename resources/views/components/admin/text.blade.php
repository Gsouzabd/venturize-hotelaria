@props([
    'prepend',
    'name',
    'class' => '',
    'value' => '',
    'disabled' => false,
    'readonly' => false,
    'append',
])

<div class="input-group">
    @isset($prepend)
        <div class="input-group-prepend">{{ $prepend }}</div>
    @endisset
    <input type="text"
           name="{{ $name }}"
           class="form-control{{ $class ? ' ' . $class : '' }}"
           value="{{ $value }}"{{ $disabled ? ' disabled' : '' }}{{ $readonly ? ' readonly' : '' }}{!! $attributes ? ' ' . $attributes : '' !!}>
    @isset($append)
        <div class="input-group-append">{{ $append }}</div>
    @endisset
</div>
