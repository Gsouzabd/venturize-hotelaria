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

    <div class="container has-sidebar">
        <div class="row">

            {{ $slot }}
            <div class="col-md-3" id="cart-col" style="display: none">
                @include('admin.reservas.partials.cart-preview')
                <div class="text-right mt-3 d-flex justify-center">
                    <x-admin.submit-btn :title="$submitTitle" style="width: 45%" :disabled=true/>
                    @if($backRoute)
                        <x-admin.cancel-btn :back-route="$backRoute" />
                    @endif
                </div>
            </div>
        </div>
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
