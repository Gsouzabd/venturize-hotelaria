@if(session()->has('notice'))
    <x-admin.alert type="dark-{{ session()->has('notice_type') ? session('notice_type') : 'success' }}"
                   class="mb-3"
                   dismissible>
        {{ session('notice') }}
    </x-admin.alert>
@endif

@if($errors->any())
    <x-admin.alert type="dark-danger" class="mb-3" dismissible>
        {{ $errors->first() }}
    </x-admin.alert>
@endif
