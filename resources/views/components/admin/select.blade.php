@props([
    'name',
    'class' => '',
    'disabled' => false,
    'placeholder' => 'Selecione um item',
    'items' => [],
    'selectedItem' => '',
])

<select name="{{ $name }}"
        class="custom-select{{ $class ? ' ' . $class : '' }}"{{ $disabled ? ' disabled' : '' }}>
    <option value="">{{ $placeholder }}</option>
    @if(!empty($items))
        @foreach($items as $itemKey => $item)
            <option value="{{ $itemKey }}"{{ (string)$itemKey === (string)$selectedItem ?? '' ? ' selected' : '' }}>
                {{ $item }}
            </option>
        @endforeach
    @endif
</select>
