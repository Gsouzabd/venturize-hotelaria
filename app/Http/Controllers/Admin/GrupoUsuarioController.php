<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GrupoUsuarioRequest;
use App\Models\GrupoUsuario;
use App\Models\Permissao;
use Illuminate\Http\Request;

class GrupoUsuarioController extends Controller
{
    private GrupoUsuario $model;

    public function __construct(GrupoUsuario $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $this->authorize('gerenciar_grupos');

        $filters = $request->all();
        $filters['nome'] ??= '';

        $query = $this->model->newQuery()->with('permissoes')->withCount('usuarios');

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        $grupos = $query
            ->orderBy('nome', 'asc')
            ->paginate(config('app.rows_per_page', 15));

        return view('admin.grupos-usuarios.index', compact('grupos', 'filters'));
    }

    public function edit($id = null)
    {
        $this->authorize('gerenciar_grupos');

        $edit = boolval($id);
        $grupo = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        if ($edit) {
            $grupo->load('permissoes');
        }

        // Garantir que todas as permissões do config existam no banco
        $permissoesConfig = config('app.enums.permissoes_plano', []);
        foreach ($permissoesConfig as $nome => $label) {
            Permissao::firstOrCreate(['nome' => $nome]);
        }

        $permissoes = Permissao::orderBy('nome')->get();
        $permissoesSelecionadas = $edit ? $grupo->permissoes->pluck('id')->toArray() : [];

        return view('admin.grupos-usuarios.form', compact('grupo', 'edit', 'permissoes', 'permissoesSelecionadas'));
    }

    public function save(GrupoUsuarioRequest $request)
    {
        $this->authorize('gerenciar_grupos');

        $data = $request->validated();
        $permissaoIds = $request->input('permissoes', []);

        if ($id = $request->get('id')) {
            $grupo = $this->model->findOrFail($id);
            $grupo->update(['nome' => $data['nome']]);
        } else {
            $grupo = $this->model->create(['nome' => $data['nome']]);
        }

        $grupo->permissoes()->sync($permissaoIds);

        return redirect()
            ->route('admin.grupos-usuarios.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $this->authorize('gerenciar_grupos');

        $grupo = $this->model->findOrFail($id);

        if ($grupo->usuarios()->count() > 0) {
            return redirect()
                ->route('admin.grupos-usuarios.index')
                ->with('error', 'Não é possível excluir este grupo pois existem usuários vinculados a ele.');
        }

        $grupo->permissoes()->detach();
        $grupo->delete();

        return redirect()
            ->route('admin.grupos-usuarios.index')
            ->with('notice', config('app.messages.delete'));
    }
}
