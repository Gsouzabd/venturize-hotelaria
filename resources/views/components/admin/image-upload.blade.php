@props([
    'id' => str($name)->slug() . '-' . uniqid(),
    'name',
    'imageUrl' => null,
])

<div id="{{ $id }}">
    @if($imageUrl)
        <div class="image-wrapper">
            <div class="row no-gutters">
                <div class="col-12 col-md-8">
                    <img src="{{ $imageUrl }}" class="img-fluid">
                </div>
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-default btn-block btn-delete mt-2 mt-md-0 ml-md-2">
                        Excluir
                    </button>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                $(function () {
                    const $imageUpload = $('#{{ $id }}');
                    const deleteImage = function () {
                        $('.image-wrapper', $imageUpload).hide();
                        $('.filepond-wrapper', $imageUpload).show();

                        $imageUpload.append(`<input type="hidden" name="{{ $name }}_excluida" value="1">`);
                    };

                    $('.filepond-wrapper', $imageUpload).hide();

                    @if($errors->any() && old($name . '_excluida'))
                    deleteImage();
                    @endif

                    $('.btn-delete', $imageUpload).on('click', function () {
                        deleteImage();
                    });
                });
            </script>
        @endpush
    @endif

    <div class="filepond-wrapper">
        <x-admin.filepond name="{{ $name }}"
                          :value="old($name)"
                          :max-file-size="config('app.image_max_size') . 'MB'"
                          :accepted-file-types="config('app.valid_image_mimetypes')"/>
        <x-admin.help-text>
            Somente arquivos {{ implode(', ', config('app.valid_image_extensions')) }}. Tamanho máximo
            de {{ config('app.image_max_size') . 'MB' }}
        </x-admin.help-text>
    </div>
</div>
