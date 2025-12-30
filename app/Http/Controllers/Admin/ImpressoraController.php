<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImpressoraRequest;
use App\Models\Impressora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImpressoraController extends Controller
{
    private Impressora $model;

    public function __construct(Impressora $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['nome'] ??= '';
        $filters['ativo'] ??= '';

        $query = $this->model->newQuery();

        if ($filters['nome']) {
            $query->where('nome', 'like', '%' . $filters['nome'] . '%');
        }

        if ($filters['ativo'] !== '') {
            $query->where('ativo', $filters['ativo']);
        }

        $impressoras = $query
            ->orderBy('ordem')
            ->orderBy('nome')
            ->paginate(config('app.rows_per_page', 15));

        return view('admin.impressoras.list', compact('impressoras', 'filters'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $impressora = $edit ? $this->model->findOrFail($id) : $this->model->newInstance();

        return view('admin.impressoras.form', compact('impressora', 'edit'));
    }

    public function save(ImpressoraRequest $request)
    {
        $data = $request->all();
        
        // Converter checkbox ativo
        $data['ativo'] = $request->has('ativo') ? true : false;
        
        // Garantir que ordem seja um número
        $data['ordem'] = $data['ordem'] ?? 0;

        if ($id = $request->get('id')) {
            $this->model->findOrFail($id)->update($data);
        } else {
            $this->model->fill($data)->save();
        }

        return redirect()
            ->route('admin.impressoras.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function destroy($id)
    {
        $impressora = $this->model->findOrFail($id);
        $impressora->delete();

        return redirect()
            ->route('admin.impressoras.index')
            ->with('notice', config('app.messages.delete'));
    }

    /**
     * Testa a conectividade com a impressora
     */
    public function testar($id): JsonResponse
    {
        try {
            $impressora = $this->model->findOrFail($id);
            
            // Testar conectividade
            $socket = @fsockopen($impressora->ip, $impressora->porta, $errno, $errstr, 3);
            
            if ($socket) {
                fclose($socket);
                return response()->json([
                    'success' => true,
                    'message' => 'Impressora acessível e respondendo corretamente.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Erro ao conectar: {$errno} - {$errstr}"
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar impressora: ' . $e->getMessage()
            ], 500);
        }
    }
}

