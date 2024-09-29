<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quarto;
use Illuminate\Http\Request;

class QuartoController extends Controller
{
    private Quarto $model;

    public function __construct(Quarto $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['andar'] ??= '';
        $filters['numero'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['andar']) {
            $query->where('andar', 'like', '%' . $filters['andar'] . '%');
        }

        if ($filters['numero']) {
            $query->where('numero', $filters['numero']);
        }

        $quartos = $query
            ->orderBy('id', 'desc')
            ->paginate(config('app.rows_per_page'));

        return view('admin.quartos.list', compact('quartos', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $quarto = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        

        return view('admin.quartos.form', compact('quarto', 'edit'));
    }

    public function save(Request $request)
    {
        $data = $request->all();


        if($data['posicao_quarto'] == null){
            $data['posicao_quarto'] = 'frente';
        }


        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.quartos.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $quarto = $this->model->findOrFail($id);
        $quarto->delete();

        return redirect()
            ->route('admin.quartos.index')
            ->with('notice', config('app.messages.delete'));
    }
}
