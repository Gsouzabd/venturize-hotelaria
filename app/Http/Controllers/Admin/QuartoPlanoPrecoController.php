<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\QuartoPlanoPreco;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuartoPlanoPrecoRequest;

class QuartoPlanoPrecoController extends Controller
{
    protected $model;

    public function __construct(QuartoPlanoPreco $model)
    {
        $this->model = $model;
    }
    public function edit($quartoId = null, $id = null)
    {
        
        $edit = $id ? true : false;
        
        $planoPreco = $edit ? $this->model::where('quarto_id', $quartoId)->findOrFail($id) : $this->model->newInstance();
    
        if($planoPreco->id == null){
            $edit = false;
            $planoPreco = $this->model->newInstance();
            $possuiPadrao = $this->model::where('quarto_id', $quartoId)
            
            ->where('is_default', 1)
            ->first();    
        }else{
            $possuiPadrao = $this->model::where('quarto_id', $planoPreco->quarto_id)
                ->where('is_default', 1)
                ->where('id', '!=', $id)
                ->first();    
        }    
        
        return view('admin.planos-preco.form', compact('planoPreco', 'edit', 'quartoId', 'possuiPadrao'));
    
    }

    public function save(QuartoPlanoPrecoRequest $request)
    {
        $id = $request->get('id');

        $planoPreco = $id ? $this->model->findOrFail($id) : $this->model->newInstance();
        $planoPreco->fill($request->all());        

        // Convert dates to yyyy/mm/dd format before saving
        $planoPreco->data_inicio = Carbon::createFromFormat('d/m/Y', $request->data_inicio)->format('Ymd');
        $planoPreco->data_fim = Carbon::createFromFormat('d/m/Y', $request->data_fim)->format('Ymd');

        $planoPreco->save();

        return redirect()->route('admin.quartos.edit', [ 'id' => $planoPreco->quarto_id] )
            ->with('notice', config('app.messages.' . ($request->get('id') ? 'update' : 'insert')));
       
    
    }

    public function delete($id)
    {
        $planoPreco = $this->model->findOrFail($id);
        $planoPreco->delete();

        return redirect()->route('admin.quartos.planos-preco.index');
    }
}