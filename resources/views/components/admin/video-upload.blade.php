@props([
    'id' => str($name)->slug() . '-' . uniqid(),
    'name',
    'videoUrl' => null,
])

<div id="{{ $id }}">
    @if($videoUrl)
        <div class="player-wrapper">
            <div class="row no-gutters">
                <div class="col-12 col-md-8">
                    <div class="embed-responsive embed-responsive-16by9">
                        <video controls class="embed-responsive-item">
                            <source src="{{ $videoUrl }}" type="video/mp4">
                        </video>
                    </div>
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
                    const $videoUpload = $('#{{ $id }}');
                    const deleteVideo = function () {
                        $('.player-wrapper', $videoUpload).hide();
                        $('.filepond-wrapper', $videoUpload).show();

                        $videoUpload.append(`<input type="hidden" name="{{ $name }}_excluido" value="1">`);
                    };

                    $('.filepond-wrapper', $videoUpload).hide();

                    @if($errors->any() && old($name . '_excluido'))
                    deleteVideo();
                    @endif

                    $('.btn-delete', $videoUpload).on('click', function () {
                        deleteVideo();
                    });
                });
            </script>
        @endpush
    @endif

    <div class="filepond-wrapper">
        <x-admin.filepond name="{{ $name }}"
                          :value="old($name)"
                          :max-file-size="config('app.video_max_size') . 'MB'"
                          :accepted-file-types="config('app.valid_video_mimetypes')"
                          chunk-uploads/>
        <x-admin.help-text>
            Somente arquivos {{ implode(', ', config('app.valid_video_extensions')) }}. Tamanho máximo
            de {{ config('app.video_max_size') . 'MB' }}
        </x-admin.help-text>
    </div>
</div>
