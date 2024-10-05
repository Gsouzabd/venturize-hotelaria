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
    <input type="hidden" name="cart_serialized" id="cart-input">

    <div class="container has-sidebar">
        <div class="row mb-5">

            {{ $slot }}
           
        </div>

        <div class="col-md-12" id="cart-col" style="display: none">
            @include('admin.reservas.partials.cart-preview')
            <div class="text-right mt-3 d-flex justify-content-end">
                <x-admin.submit-btn :title="$submitTitle" style="width: 45%" :disabled=true/>
                @if($backRoute)
                    <x-admin.cancel-btn :back-route="$backRoute" />
                @endif
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


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.edit-form').addEventListener('submit', function(event) {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                document.getElementById('cart-input').value = JSON.stringify(cart);
            });
        });
    </script>
@endpush