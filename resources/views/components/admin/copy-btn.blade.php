<button class="btn {{ $class ?? 'btn-secondary' }} btn-copy" type="button" data-copy="{{ $copy }}">Copiar</button>

@pushonce('scripts')
    <script>
        $(function () {
            $('.btn-copy').on('click', function () {
                navigator.clipboard.writeText($(this).data('copy'));
                Swal.fire("Feito!", "Copiado para a área de transferência com sucesso!", "success")
            });
        });
    </script>
@endpushonce
