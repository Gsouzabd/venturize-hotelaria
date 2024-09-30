@props(['title' => 'Enviar', 'disabled' => false])

<button type="submit" class="btn btn-primary ml-md-2 mb-2 mb-md-0" {{ $disabled ? 'disabled' : '' }}>
    {{ $title }}
</button>