@props(['type' => 'primary'])

<div {{ $attributes->merge(['class' => 'callout callout-' . $type]) }}>
    {{ $slot }}
</div>
