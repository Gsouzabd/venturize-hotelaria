<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsuarioRequest;
use App\Mail\NovaSenha;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\GrupoUsuario;

class UsuarioController extends Controller
{
    private Usuario $model;

    public function __construct(Usuario $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {  

        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['email'] ??= '';
        $filters['tipo'] ??= '';
        $filters['fl_ativo'] ??= null;

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        if ($filters['email']) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if ($filters['tipo']) {
            $query->where('tipo', $filters['tipo']);
        }

        if (!is_null($filters['fl_ativo'])) {
            $query->where('fl_ativo', $filters['fl_ativo']);
        }

        $usuarios = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.usuarios.list', compact('usuarios', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $usuario = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        $gruposUsuarios = GrupoUsuario::pluck('nome', 'id')->toArray();


        return view('admin.usuarios.form', compact('usuario', 'edit', 'gruposUsuarios'));
    }

    public function save(UsuarioRequest $request)
    {
        $data = $request->all();
        $data['fl_ativo'] ??= 0;

        if ($id = $request->get('id')) {
            if (!$data['senha']) {
                unset($data['senha']);
            }

            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.usuarios.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $usuario = $this->model->findOrFail($id);
        abort_if($usuario->reservas()->exists(), 403, 'Não é possível excluir porque está vinculado a uma reserva.');

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('notice', config('app.messages.delete'));
    }

    public function resendPassword($id)
    {
        $usuario = $this->model->findOrFail($id);

        $usuario->senha = $senha = Str::random(8);
        $usuario->save();

        Mail::to($usuario->email)->send(new NovaSenha($usuario->nome, $usuario->email, $senha));

        return response()->json([
            'success' => true,
            'message' => config('app.messages.send_new_password'),
        ]);
    }
}
