<form action="{{ route($route, $routeParams) }}" method="post" class="form-delete d-inline">
    @method('DELETE')
    @csrf
    <button type="submit" class="btn btn-xs btn-danger">Excluir</button>
</form>

@pushonce('scripts')
    <script>
        $(function () {
            $('.form-delete').on('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: "Deseja realmente excluir?",
                    text: "Essa ação é irreversível!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    cancelButtonText: "Cancelar",
                    confirmButtonText: "Excluir definitivamente"
                }).then(function (result) {
                    if (result.value) {
                        e.target.submit();
                    }
                });

                return false;
            });
        });
    </script>
@endpushonce
