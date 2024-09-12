@props([
    'saveRoute',
    'method' => 'post',
    'class' => '',
    'filesEnctype' => false,
    'isEdit' => false,
    'ajaxRequest' => false,
    'submitTitle' => null,
    'backRoute' => '',
])

<form action="{{ route($saveRoute) }}" method="{{ $method }}"
      class="edit-form{{ $class ? ' ' . $class : '' }}"{!! ($filesEnctype ? ' enctype="multipart/form-data"' : '') . ($attributes ? ' ' . $attributes : '') !!}>
    @if($isEdit)
        @method('PUT')
    @endif
    @if($method === 'post')
        @csrf
    @endif
    {{ $slot }}

    <div class="text-right mt-3">
        <x-admin.submit-btn :title="$submitTitle"/>
        @if($backRoute)
            <x-admin.cancel-btn :back-route="$backRoute"/>
        @endif
    </div>
</form>

@if($ajaxRequest)
    @push('scripts')
        <script>
            $(function () {
                appHelpers.doAjaxForm('.edit-form');
            });
        </script>
    @endpush
@endif
