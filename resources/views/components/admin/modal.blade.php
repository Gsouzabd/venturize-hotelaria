@props([
    'animated'=> true,
    'id',
    'staticBackdrop' => false,
    'scrollable' => false,
    'centered' => false,
    'size',
    'title',
    'footer',
])

<div class="modal{{ $animated ? ' fade' : '' }}" id="{{ $id }}"
     {!! $staticBackdrop ? ' data-backdrop="static"' : '' !!} data-keyboard="false" tabindex="-1"
     aria-labelledby="{{ $id }}-label" aria-hidden="true">
    <div
        class="modal-dialog{{ ($scrollable ? ' modal-dialog-scrollable' : '') . ($centered ? ' modal-dialog-centered' : '') . (isset($size) ? ' modal-'.$size : '') }}">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}-label">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
