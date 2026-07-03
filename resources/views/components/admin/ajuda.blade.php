@props(['titulo' => 'Como funciona esta tela?'])

<details {{ $attributes->merge(['class' => 'mb-3']) }}>
    <summary class="text-primary" style="cursor: pointer; outline: none; user-select: none;">
        <i class="fas fa-question-circle"></i> {{ $titulo }}
    </summary>
    <div class="callout callout-info mt-2 mb-0" style="max-height: 220px; overflow-y: auto;">
        {{ $slot }}
    </div>
</details>
