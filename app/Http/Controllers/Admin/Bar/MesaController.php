<?php

namespace App\Http\Controllers\Admin\Bar;

use App\Models\Bar\Mesa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MesaController extends Controller
{
    private Mesa $model;

    public function __construct(Mesa $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['numero'] ??= '';
        $filters['status'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['numero']) {
            $query->where('numero', 'like', '%' . $filters['numero'] . '%');
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        $mesas = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.bar.mesas.list', compact('mesas', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $mesa = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.bar.mesas.form', compact('mesa', 'edit'));
    }

    public function save(Request $request)
    {
        $data = $request->all();

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.bar.mesas.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $mesa = $this->model->findOrFail($id);
        $mesa->delete();

        return redirect()
            ->route('admin.bar.mesas.index')
            ->with('notice', config('app.messages.delete'));
    }
}