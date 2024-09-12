@props([
    'id' => str($name)->slug() . '-' . uniqid(),
    'name',
    'class' => '',
    'remoteUrl' => '',
    'minInputLength' => '',
    'placeholder' => 'Selecione um item',
    'items' => [],
    'selectedItem' => '',
    'remoteUrlSelectedValue' => '',
    'remoteUrlSelectedText' => '',
])

<div class="position-relative">
    <select id="{{ $id }}"
            name="{{ $name }}"
            class="form-select2 form-control{{ $class ? ' ' . $class : '' }}"
            style="width: 100%"
            data-allow-clear="true"
            {!! $remoteUrl ? ' data-ajax--cache="false" data-ajax--delay="500" data-ajax--url="' . $remoteUrl . '"' : '' !!}
            {!! $minInputLength ? ' data-minimum-input-length="' . $minInputLength . '"' : '' !!}
            data-placeholder="{{ $placeholder }}">
        <option value="">{{ $placeholder }}</option>
        @if(!empty($items))
            @foreach($items as $itemKey => $item)
                <option value="{{ $itemKey }}"{{ (string)$itemKey === (string)$selectedItem ? ' selected' : '' }}>
                    {{ $item }}
                </option>
            @endforeach
        @endif

        @if($remoteUrlSelectedValue && $remoteUrlSelectedText)
            <option value="{{ $remoteUrlSelectedValue }}" selected>
                {{ $remoteUrlSelectedText }}
            </option>
        @endif
    </select>
</div>

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/select2/select2.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/select2/select2.pt-BR.js') }}"></script>
    <script>
        $(function () {
            // FIX SELECT2 AUTOFOCUS...
            // https://stackoverflow.com/questions/68030101/why-is-jquery-select2-autofocus-not-working-and-how-do-i-fix-it
            $(document).on('select2:open', function () {
                window.setTimeout(function () {
                    document.querySelector('input.select2-search__field').focus();
                }, 0);
            });
        });
    </script>
@endpushonce

@push('scripts')
    <script>
        $(function () {
            $('#{{ $id }}').select2({
                dropdownParent: $('#{{ $id }}').parent(),
                language: 'pt-BR'
            });
        });
    </script>
@endpush
