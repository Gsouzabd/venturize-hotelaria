@props([
    'id' => str($name)->slug() . '-' . uniqid(),
    'name',
    'transcriptionFileUrl' => null,
    'transcription' => null,
])

<div id="{{ $id }}">
    @if($transcriptionFileUrl)
        <div class="transcription-wrapper">
            <div class="row no-gutters">
                <div class="col-12 col-md-8">
                    <x-admin.callout type="secondary">
                        <div style="max-height: 320px" class="overflow-scroll">
                            @foreach($transcription as $row)
                                <div class="row no-gutters mb-2">
                                    <div class="col flex-grow-0">
                                        <strong>{{ $row->start_humanized }}</strong>
                                    </div>
                                    <div class="col ml-2">
                                        @foreach($row->lines as $line)
                                            {{ $line }}<br>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-admin.callout>
                </div>
                <div class="col-12 col-md-4">
                    <button type="button" class="btn btn-default btn-block btn-delete mt-2 mt-md-0 ml-md-2">
                        Excluir
                    </button>
                    <a href="{{ $transcriptionFileUrl }}" class="btn btn-default btn-block mt-2 ml-md-2">
                        Arquivo texto
                    </a>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                $(function () {
                    const $transcriptionUpload = $('#{{ $id }}');
                    const deleteTranscription = function () {
                        $('.transcription-wrapper', $transcriptionUpload).hide();
                        $('.filepond-wrapper', $transcriptionUpload).show();

                        $transcriptionUpload.append(`<input type="hidden" name="{{ $name }}_excluido" value="1">`);
                    };

                    $('.filepond-wrapper', $transcriptionUpload).hide();

                    @if($errors->any() && old($name . '_excluido'))
                    deleteTranscription();
                    @endif

                    $('.btn-delete', $transcriptionUpload).on('click', function () {
                        deleteTranscription();
                    });
                });
            </script>
        @endpush
    @endif

    <div class="filepond-wrapper">
        <x-admin.filepond name="{{ $name }}"
                          :value="old($name)"
                          :max-file-size="config('app.transcription_max_size') . 'MB'"
                          :accepted-file-types="config('app.valid_transcription_mimetypes')"/>
        <x-admin.help-text>
            Somente arquivos {{ implode(', ', config('app.valid_transcription_extensions')) }}. Tamanho máximo
            de {{ config('app.transcription_max_size') . 'MB' }}
        </x-admin.help-text>
    </div>
</div>
