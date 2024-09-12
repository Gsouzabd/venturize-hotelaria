@props([
    'name',
    'class' => '',
    'rows' => 3,
    'disabled' => false,
    'value' => '',
])

<textarea name="{{ $name }}"
          class="form-control{{ $class ? ' ' . $class : '' }}"
          rows="{{ $rows }}"{{ $disabled ? ' disabled' : '' }}>{{ $value }}</textarea>
