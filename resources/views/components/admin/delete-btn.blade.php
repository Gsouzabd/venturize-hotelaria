<form method="POST" action="{{ route($route, $routeParams) }}" class="d-inline-block delete-form">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="btn btn-xs btn-outline-danger delete-btn"
            onclick="return confirm('Tem certeza que deseja excluir este item?')">
        <i class="fas fa-trash-alt"></i> Excluir
    </button>
</form>
