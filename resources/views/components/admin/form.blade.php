@props([
    'saveRoute',
    'routeParams' => [],
    'method' => 'post',
    'class' => '',
    'filesEnctype' => false,
    'isEdit' => false,
    'ajaxRequest' => false,
    'submitTitle' => null,
    'backRoute' => '',
    'backRouteParams' => [],
])

<form action="{{ route($saveRoute, $routeParams) }}" method="{{ $method }}"
      class="edit-form{{ $class ? ' ' . $class : '' }}" {!! $filesEnctype ? 'enctype="multipart/form-data"' : '' !!} {{ $attributes->merge(['class' => '']) }}>
    @if($isEdit)
        @method('PUT')
    @endif
    @csrf
    {{ $slot }}

    <div class="text-right mt-3">
        <x-admin.submit-btn :title="$submitTitle"/>
        @if($backRoute)
            <x-admin.cancel-btn :back-route="$backRoute" :route-params="$backRouteParams"/>
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