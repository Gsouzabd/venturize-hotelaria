@props([
    'class' => '',
    'stacked' => false,
    'name',
    'value' => 1,
    'checked' => false,
    'disabled' => false,
    'label' => '',
])

<div class="switcher-wrapper{{ $class ? ' ' . $class : '' }}">
    <label class="switcher switcher-square">
        <input type="{{ $stacked ? 'radio' : 'checkbox' }}"
               name="{{ $name }}"
               class="switcher-input"
               value="{{ $value }}"{{ $checked ? ' checked' : '' }}{{ $disabled ? ' disabled' : '' }}>
        <span class="switcher-indicator">
            <span class="switcher-yes"></span>
            <span class="switcher-no"></span>
        </span>
        @if($label)
            <span class="switcher-label">{{ $label }}</span>
        @endif
    </label>
</div>
