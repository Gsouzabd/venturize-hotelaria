<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Quarto;
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
        
        return view('admin.planos-preco.form', compact('planoPreco', 'edit', 'quartoId', ));
    
    }

    public function save(QuartoPlanoPrecoRequest $request)
    {
        $id = $request->get('id');

        // dd($request->all());

        $planoPreco = $id ? $this->model->findOrFail($id) : $this->model->newInstance();
        $planoPreco->fill($request->all());

        // Convert dates to yyyy/mm/dd format before saving
        $planoPreco->data_inicio = parseDateVenturize($request->data_inicio);
        $planoPreco->data_fim = parseDateVenturize($request->data_fim);

        // Handle radio button values
        $tipoQuarto = $request->input('tipo_quarto');
        $planoPreco->is_individual = ($tipoQuarto == 'individual');
        $planoPreco->is_duplo = ($tipoQuarto == 'duplo');
        $planoPreco->is_triplo = ($tipoQuarto == 'triplo');

        $planoPreco->save();

        $quartosCopiadosCount = 0;
        if ($request->boolean('copiar_para_categoria') && $id) {
            $quartoOrigem = Quarto::find($planoPreco->quarto_id);
            if ($quartoOrigem) {
                $precosDia = [
                    'preco_segunda'  => $planoPreco->preco_segunda,
                    'preco_terca'    => $planoPreco->preco_terca,
                    'preco_quarta'   => $planoPreco->preco_quarta,
                    'preco_quinta'   => $planoPreco->preco_quinta,
                    'preco_sexta'    => $planoPreco->preco_sexta,
                    'preco_sabado'   => $planoPreco->preco_sabado,
                    'preco_domingo'  => $planoPreco->preco_domingo,
                ];

                $quartosCategoria = Quarto::where('classificacao', $quartoOrigem->classificacao)
                    ->where('id', '!=', $quartoOrigem->id)
                    ->get();

                foreach ($quartosCategoria as $quarto) {
                    $planoExistente = QuartoPlanoPreco::where('quarto_id', $quarto->id)
                        ->where('is_individual', $planoPreco->is_individual)
                        ->where('is_duplo', $planoPreco->is_duplo)
                        ->where('is_triplo', $planoPreco->is_triplo)
                        ->where('is_default', $planoPreco->is_default)
                        ->first();

                    if ($planoExistente) {
                        $planoExistente->update($precosDia);
                    } else {
                        QuartoPlanoPreco::create(array_merge($precosDia, [
                            'quarto_id'     => $quarto->id,
                            'is_individual' => $planoPreco->is_individual,
                            'is_duplo'      => $planoPreco->is_duplo,
                            'is_triplo'     => $planoPreco->is_triplo,
                            'is_default'    => $planoPreco->is_default,
                            'data_inicio'   => $planoPreco->data_inicio,
                            'data_fim'      => $planoPreco->data_fim,
                        ]));
                    }
                    $quartosCopiadosCount++;
                }
            }
        }

        $notice = config('app.messages.' . ($request->get('id') ? 'update' : 'insert'));
        if ($quartosCopiadosCount > 0) {
            $notice .= " Preços aplicados a {$quartosCopiadosCount} quarto(s) da mesma categoria.";
        }

        return redirect()->route('admin.quartos.edit', ['id' => $planoPreco->quarto_id])
            ->with('notice', $notice);
    }

    public function delete($id)
    {
        $planoPreco = $this->model->findOrFail($id);
        $planoPreco->delete();

        return redirect()->route('admin.quartos.planos-preco.index');
    }
}