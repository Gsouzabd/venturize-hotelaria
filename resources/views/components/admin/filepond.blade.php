@props([
    'name',
    'value' => '',
    'storeAsFile' => false,
    'chunkUploads' => false,
    'maxFileSize' => null,
    'acceptedFileTypes' => [],
])

<input type="file"
       name="{{ $name }}">

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/filepond/filepond.min.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/filepond/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/filepond/filepond.min.js') }}"></script>

    <script>
        FilePond.registerPlugin(FilePondPluginFileValidateSize);
        FilePond.registerPlugin(FilePondPluginFileValidateType);

        FilePond.setOptions({
            labelIdle: 'Arraste e solte os arquivos ou <span class="filepond--label-action"> Clique aqui </span>',
            labelInvalidField: 'Arquivos inválidos',
            labelFileWaitingForSize: 'Calculando o tamanho do arquivo',
            labelFileSizeNotAvailable: 'Tamanho do arquivo indisponível',
            labelFileLoading: 'Carregando',
            labelFileLoadError: 'Erro durante o carregamento',
            labelFileProcessing: 'Enviando',
            labelFileProcessingComplete: 'Envio finalizado',
            labelFileProcessingAborted: 'Envio cancelado',
            labelFileProcessingError: 'Erro durante o envio',
            labelFileProcessingRevertError: 'Erro ao reverter o envio',
            labelFileRemoveError: 'Erro ao remover o arquivo',
            labelTapToCancel: 'clique para cancelar',
            labelTapToRetry: 'clique para reenviar',
            labelTapToUndo: 'clique para desfazer',
            labelButtonRemoveItem: 'Remover',
            labelButtonAbortItemLoad: 'Abortar',
            labelButtonRetryItemLoad: 'Reenviar',
            labelButtonAbortItemProcessing: 'Cancelar',
            labelButtonUndoItemProcessing: 'Desfazer',
            labelButtonRetryItemProcessing: 'Reenviar',
            labelButtonProcessItem: 'Enviar',
            labelMaxFileSizeExceeded: 'Arquivo é muito grande',
            labelMaxFileSize: 'O tamanho máximo permitido: {filesize}',
            labelMaxTotalFileSizeExceeded: 'Tamanho total dos arquivos excedido',
            labelMaxTotalFileSize: 'Tamanho total permitido: {filesize}',
            labelFileTypeNotAllowed: 'Tipo de arquivo inválido',
            fileValidateTypeLabelExpectedTypes: 'Tipos de arquivo suportados são {allButLastType} ou {lastType}',
            fileValidateTypeLabelExpectedTypesMap: @json((object)config('app.file_mimetypes_map')),
            imageValidateSizeLabelFormatError: 'Tipo de imagem inválida',
            imageValidateSizeLabelImageSizeTooSmall: 'Imagem muito pequena',
            imageValidateSizeLabelImageSizeTooBig: 'Imagem muito grande',
            imageValidateSizeLabelExpectedMinSize: 'Tamanho mínimo permitida: {minWidth} × {minHeight}',
            imageValidateSizeLabelExpectedMaxSize: 'Tamanho máximo permitido: {maxWidth} × {maxHeight}',
            imageValidateSizeLabelImageResolutionTooLow: 'Resolução muito baixa',
            imageValidateSizeLabelImageResolutionTooHigh: 'Resolução muito alta',
            imageValidateSizeLabelExpectedMinResolution: 'Resolução mínima permitida: {minResolution}',
            imageValidateSizeLabelExpectedMaxResolution: 'Resolução máxima permitida: {maxResolution}',
        });
    </script>
@endpushonce

@push('scripts')
    <script>
        $(function () {
            FilePond.create(document.querySelector('input[name="{{ $name }}"]'), {
                @if($storeAsFile)
                storeAsFile: true,
                @else
                server: {
                    url: '{{ config('filepond.server.url') }}',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    }
                },
                @props([
    'name',
    'value' => '',
    'storeAsFile' => false,
    'chunkUploads' => false,
    'maxFileSize' => null,
    'acceptedFileTypes' => [],

])

<input type="file"
       name="{{ $name }}">

@pushonce('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/vendor/filepond/filepond.min.css') }}">
@endpushonce

@pushonce('scripts')
    <script src="{{ asset('assets/admin/vendor/filepond/filepond-plugin-file-validate-size.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/filepond/filepond-plugin-file-validate-type.min.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/filepond/filepond.min.js') }}"></script>

    <script>
        FilePond.registerPlugin(FilePondPluginFileValidateSize);
        FilePond.registerPlugin(FilePondPluginFileValidateType);

        FilePond.setOptions({
            labelIdle: 'Arraste e solte os arquivos ou <span class="filepond--label-action"> Clique aqui </span>',
            labelInvalidField: 'Arquivos inválidos',
            labelFileWaitingForSize: 'Calculando o tamanho do arquivo',
            labelFileSizeNotAvailable: 'Tamanho do arquivo indisponível',
            labelFileLoading: 'Carregando',
            labelFileLoadError: 'Erro durante o carregamento',
            labelFileProcessing: 'Enviando',
            labelFileProcessingComplete: 'Envio finalizado',
            labelFileProcessingAborted: 'Envio cancelado',
            labelFileProcessingError: 'Erro durante o envio',
            labelFileProcessingRevertError: 'Erro ao reverter o envio',
            labelFileRemoveError: 'Erro ao remover o arquivo',
            labelTapToCancel: 'clique para cancelar',
            labelTapToRetry: 'clique para reenviar',
            labelTapToUndo: 'clique para desfazer',
            labelButtonRemoveItem: 'Remover',
            labelButtonAbortItemLoad: 'Abortar',
            labelButtonRetryItemLoad: 'Reenviar',
            labelButtonAbortItemProcessing: 'Cancelar',
            labelButtonUndoItemProcessing: 'Desfazer',
            labelButtonRetryItemProcessing: 'Reenviar',
            labelButtonProcessItem: 'Enviar',
            labelMaxFileSizeExceeded: 'Arquivo é muito grande',
            labelMaxFileSize: 'O tamanho máximo permitido: {filesize}',
            labelMaxTotalFileSizeExceeded: 'Tamanho total dos arquivos excedido',
            labelMaxTotalFileSize: 'Tamanho total permitido: {filesize}',
            labelFileTypeNotAllowed: 'Tipo de arquivo inválido',
            fileValidateTypeLabelExpectedTypes: 'Tipos de arquivo suportados são {allButLastType} ou {lastType}',
            fileValidateTypeLabelExpectedTypesMap: @json((object)config('app.file_mimetypes_map')),
            imageValidateSizeLabelFormatError: 'Tipo de imagem inválida',
            imageValidateSizeLabelImageSizeTooSmall: 'Imagem muito pequena',
            imageValidateSizeLabelImageSizeTooBig: 'Imagem muito grande',
            imageValidateSizeLabelExpectedMinSize: 'Tamanho mínimo permitida: {minWidth} × {minHeight}',
            imageValidateSizeLabelExpectedMaxSize: 'Tamanho máximo permitido: {maxWidth} × {maxHeight}',
            imageValidateSizeLabelImageResolutionTooLow: 'Resolução muito baixa',
            imageValidateSizeLabelImageResolutionTooHigh: 'Resolução muito alta',
            imageValidateSizeLabelExpectedMinResolution: 'Resolução mínima permitida: {minResolution}',
            imageValidateSizeLabelExpectedMaxResolution: 'Resolução máxima permitida: {maxResolution}',
        });
    </script>
@endpushonce

@push('scripts')
    <script>
        $(function () {
            FilePond.create(document.querySelector('input[name="{{ $name }}"]'), {
                @if($storeAsFile)
                storeAsFile: true,
                @else
                server: {
                    url: '{{ config('filepond.server.url') }}',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    }
                },
                chunkUploads: @json($chunkUploads),
                @endif
                maxFileSize: @json($maxFileSize),
                fileValidateTypeDetectType: (source, type) => {
                    console.log('Detected MIME type:', type);
            
                    if (!type) {
                        const extension = source.name.split('.').pop().toLowerCase();
            
    
                        if (extension === 'vtt') {
                            type = 'text/vtt';
                        } else if (extension === 'srt') {
                            type = 'application/x-subrip';
                        } else if (extension === 'txt') {
                            type = 'text/plain';
                        }
            
                        console.log('Fallback MIME type:', type);
                    }
            
                    return type;
                },
                @if($value)
                files: [{source: '{{ $value }}', options: {type: 'limbo'}}],
                @endif
            });
        });
    </script>
@endpush

                chunkUploads: @json($chunkUploads),
                @endif
                maxFileSize: @json($maxFileSize),
                acceptedFileTypes: @json($acceptedFileTypes),
                @if($value)
                files: [{source: '{{ $value }}', options: {type: 'limbo'}}],
                @endif
            });
        });
    </script>
@endpush
