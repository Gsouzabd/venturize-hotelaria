@props([
    'inline'=> false,
    'label',
    'required'=> false,
])

<div class="form-label{{ $inline ? ' d-inline-block' : '' }}">
    {!! $label . ($required ? ' <span class="required">*</span>' : '') !!}
</div>
