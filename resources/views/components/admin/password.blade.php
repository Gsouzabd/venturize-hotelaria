@props([
    'name',
    'class' => '',
    'value' => '',
    'disabled' => false,
    'readonly' => false,
])

<div class="input-group">
    <input type="password"
           name="{{ $name }}"
           class="form-control{{ $class ? ' ' . $class : '' }}"
           value="{{ $value }}"{{ $disabled ? ' disabled' : '' }}{{ $readonly ? ' readonly' : '' }}{!! $attributes ? ' ' . $attributes : '' !!}>
</div>
