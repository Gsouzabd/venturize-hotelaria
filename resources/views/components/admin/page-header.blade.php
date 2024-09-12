@props([
    'title',
    'description' => null,
])

<h4 class="page-header d-flex justify-content-between align-items-md-center flex-column flex-md-row w-100 pb-3 mb-0">
    <div class="font-weight-bold">
        {!! $title !!}

        @if($description)
            <div class="text-muted text-tiny">
                <small class="font-weight-normal">{{ $description }}</small>
            </div>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="page-header-actions d-md-flex align-items-center mt-2 mt-md-0">
            {{ $slot }}
        </div>
    @endif
</h4>
