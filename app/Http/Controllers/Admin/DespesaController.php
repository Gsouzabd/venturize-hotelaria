<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Despesa;
use App\Models\CategoriaDespesa;
use App\Models\DespesaCategoria;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DespesaRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Services\ExcelExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DespesaController extends Controller
{
    private Despesa $model;

    public function __construct(Despesa $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['numero_nota_fiscal'] ??= '';
        $filters['data_inicial'] ??= '';
        $filters['data_final'] ??= '';
        $filters['categoria_id'] ??= '';
        $filters['fornecedor_id'] ??= '';

        $query = $this->model->with(['usuario', 'despesaCategorias.categoriaDespesa', 'fornecedor']);

        if ($filters['numero_nota_fiscal']) {
            $query->where('numero_nota_fiscal', 'like', '%' . $filters['numero_nota_fiscal'] . '%');
        }

        if ($filters['data_inicial']) {
            $dataInicial = Carbon::createFromFormat('d/m/Y', $filters['data_inicial'])->format('Y-m-d');
            $query->where('data', '>=', $dataInicial);
        }

        if ($filters['data_final']) {
            $dataFinal = Carbon::createFromFormat('d/m/Y', $filters['data_final'])->format('Y-m-d');
            $query->where('data', '<=', $dataFinal);
        }

        if ($filters['categoria_id']) {
            $query->whereHas('despesaCategorias', function ($q) use ($filters) {
                $q->where('categoria_despesa_id', $filters['categoria_id']);
            });
        }

        if ($filters['fornecedor_id']) {
            $query->where('fornecedor_id', $filters['fornecedor_id']);
        }

        $despesas = $query
            ->orderBy('created_at', 'desc')
            ->paginate(config('app.rows_per_page', 15));

        $categorias = CategoriaDespesa::ativas()->orderBy('nome')->get();
        $fornecedores = Fornecedor::orderBy('nome')->get();

        return view('admin.despesas.index', compact('despesas', 'filters', 'categorias', 'fornecedores'));
    }

    public function edit($id = null)
    {
        $edit = boolval($id);
        $despesa = $edit ? $this->model->with(['despesaCategorias.categoriaDespesa', 'fornecedor'])->findOrFail($id) : $this->model->newInstance();
        $categorias = CategoriaDespesa::ativas()->orderBy('nome')->get();
        // Não precisamos mais carregar todos os fornecedores, pois usamos busca AJAX
        // Mas mantemos para compatibilidade caso necessário
        $fornecedores = collect([]);

        return view('admin.despesas.form', compact('despesa', 'edit', 'categorias', 'fornecedores'));
    }

    public function save(DespesaRequest $request)
    {
        $data = $request->validated();
        $edit = $request->has('id');
        $despesa = null;
        
        if ($edit) {
            $despesa = $this->model->findOrFail($request->get('id'));
        }
        
        // Processar arquivo se fornecido
        if ($request->hasFile('arquivo_nota')) {
            $request->validate([
                'arquivo_nota' => 'file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            ]);
            
            $file = $request->file('arquivo_nota');
            
            // Criar diretório se não existir
            $directory = 'despesas';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }
            
            // Gerar nome único para o arquivo
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($directory, $filename, 'public');
            
            $data['arquivo_nota'] = $path;
        } elseif ($edit && $despesa && !$request->has('arquivo_nota')) {
            // Se estiver editando e não foi enviado novo arquivo, manter o existente
            $data['arquivo_nota'] = $despesa->arquivo_nota;
        }

        // Converter data do formato brasileiro
        if (isset($data['data']) && str_contains($data['data'], '/')) {
            $data['data'] = Carbon::createFromFormat('d/m/Y', $data['data'])->format('Y-m-d');
        }

        // Tratar fornecedor_id vazio como null
        if (isset($data['fornecedor_id']) && empty($data['fornecedor_id'])) {
            $data['fornecedor_id'] = null;
        }

        // Tratar numero_nota_fiscal vazio como null
        if (isset($data['numero_nota_fiscal']) && empty($data['numero_nota_fiscal'])) {
            $data['numero_nota_fiscal'] = null;
        }

        // Adicionar usuário que cadastrou
        $data['usuario_id'] = Auth::id();

        // Salvar despesa
        if ($id = $request->get('id')) {
            $despesa = $this->model->findOrFail($id);
            
            // Remover arquivo antigo se houver novo
            if (isset($data['arquivo_nota']) && $despesa->arquivo_nota && $despesa->arquivo_nota !== $data['arquivo_nota']) {
                Storage::disk('public')->delete($despesa->arquivo_nota);
            }
            
            $despesa->update($data);
        } else {
            $despesa = $this->model->create($data);
        }

        // Salvar rateios
        $rateios = $request->input('rateios', []);
        
        // Remover rateios antigos
        $despesa->despesaCategorias()->delete();
        
        // Criar novos rateios
        foreach ($rateios as $rateio) {
            if (!empty($rateio['valor']) && $rateio['valor'] > 0) {
                DespesaCategoria::create([
                    'despesa_id' => $despesa->id,
                    'categoria_despesa_id' => $rateio['categoria_despesa_id'] ?? null,
                    'valor' => $rateio['valor'],
                    'observacoes' => $rateio['observacoes'] ?? null,
                ]);
            }
        }

        return redirect()
            ->route('admin.despesas.index')
            ->with('notice', config('app.messages.' . ($id ? 'update' : 'insert')));
    }

    public function show($id)
    {
        $despesa = $this->model->with(['usuario', 'fornecedor', 'despesaCategorias.categoriaDespesa'])->findOrFail($id);
        
        return view('admin.despesas.show', compact('despesa'));
    }

    public function destroy($id)
    {
        $despesa = $this->model->findOrFail($id);
        
        // Remover arquivo se existir
        if ($despesa->arquivo_nota) {
            Storage::disk('public')->delete($despesa->arquivo_nota);
        }
        
        $despesa->delete();

        return redirect()
            ->route('admin.despesas.index')
            ->with('notice', config('app.messages.delete'));
    }

    public function relatorios(Request $request)
    {
        $filters = $request->all();
        $filters['data_inicial'] ??= Carbon::now()->startOfMonth()->format('d/m/Y');
        $filters['data_final'] ??= Carbon::now()->format('d/m/Y');
        $filters['categoria_id'] ??= '';

        $dataInicial = Carbon::createFromFormat('d/m/Y', $filters['data_inicial'])->startOfDay();
        $dataFinal = Carbon::createFromFormat('d/m/Y', $filters['data_final'])->endOfDay();

        $query = $this->model->whereBetween('data', [$dataInicial, $dataFinal]);

        if ($filters['categoria_id']) {
            $query->whereHas('despesaCategorias', function ($q) use ($filters) {
                $q->where('categoria_despesa_id', $filters['categoria_id']);
            });
        }

        $despesas = $query->with('despesaCategorias.categoriaDespesa')->get();

        // Consolidar por categoria
        $consolidado = [];
        $totalGeral = 0;

        foreach ($despesas as $despesa) {
            foreach ($despesa->despesaCategorias as $despesaCategoria) {
                $categoriaNome = $despesaCategoria->categoriaDespesa ? $despesaCategoria->categoriaDespesa->nome : 'Sem categoria';
                $categoriaId = $despesaCategoria->categoria_despesa_id ?? 'sem_categoria';
                
                if (!isset($consolidado[$categoriaId])) {
                    $consolidado[$categoriaId] = [
                        'nome' => $categoriaNome,
                        'valor' => 0,
                        'quantidade' => 0,
                    ];
                }
                
                $consolidado[$categoriaId]['valor'] += $despesaCategoria->valor;
                $consolidado[$categoriaId]['quantidade']++;
                $totalGeral += $despesaCategoria->valor;
            }
        }

        $categorias = CategoriaDespesa::ativas()->orderBy('nome')->get();

        return view('admin.despesas.relatorios', compact('consolidado', 'totalGeral', 'filters', 'categorias', 'despesas'));
    }

    public function exportarConsolidado(Request $request)
    {
        $filters = $request->all();
        $filters['data_inicial'] ??= Carbon::now()->startOfMonth()->format('d/m/Y');
        $filters['data_final'] ??= Carbon::now()->format('d/m/Y');
        $filters['categoria_id'] ??= '';

        $dataInicial = Carbon::createFromFormat('d/m/Y', $filters['data_inicial'])->startOfDay();
        $dataFinal = Carbon::createFromFormat('d/m/Y', $filters['data_final'])->endOfDay();

        $query = $this->model->whereBetween('data', [$dataInicial, $dataFinal]);

        if ($filters['categoria_id']) {
            $query->whereHas('despesaCategorias', function ($q) use ($filters) {
                $q->where('categoria_despesa_id', $filters['categoria_id']);
            });
        }

        $despesas = $query->with('despesaCategorias.categoriaDespesa')->get();

        // Consolidar por categoria
        $consolidado = [];
        $totalGeral = 0;

        foreach ($despesas as $despesa) {
            foreach ($despesa->despesaCategorias as $despesaCategoria) {
                $categoriaNome = $despesaCategoria->categoriaDespesa 
                    ? $despesaCategoria->categoriaDespesa->nome 
                    : 'Sem categoria';
                $categoriaId = $despesaCategoria->categoria_despesa_id ?? 'sem_categoria';
                
                if (!isset($consolidado[$categoriaId])) {
                    $consolidado[$categoriaId] = [
                        'nome' => $categoriaNome,
                        'valor' => 0,
                        'quantidade' => 0,
                    ];
                }
                
                $consolidado[$categoriaId]['valor'] += $despesaCategoria->valor;
                $consolidado[$categoriaId]['quantidade']++;
                $totalGeral += $despesaCategoria->valor;
            }
        }

        $consolidadoArray = array_values($consolidado);

        // Preparar dados para Excel
        $dadosExcel = [];
        
        // Título e período
        $dadosExcel[] = ['Relatório Consolidado de Despesas'];
        $dadosExcel[] = ['Período: ' . $dataInicial->format('d/m/Y') . ' a ' . $dataFinal->format('d/m/Y')];
        $dadosExcel[] = [];
        
        // Cabeçalhos
        $dadosExcel[] = ['Categoria', 'Quantidade', 'Valor Total', 'Percentual'];
        
        // Dados
        foreach ($consolidadoArray as $item) {
            $percentual = $totalGeral > 0 ? number_format(($item['valor'] / $totalGeral) * 100, 2, ',', '.') : '0,00';
            $dadosExcel[] = [
                $item['nome'],
                $item['quantidade'],
                number_format($item['valor'], 2, ',', '.'),
                $percentual . '%'
            ];
        }
        
        // Total
        $dadosExcel[] = [];
        $dadosExcel[] = ['TOTAL GERAL', '', number_format($totalGeral, 2, ',', '.'), '100,00%'];

        // Gerar arquivo Excel
        $filename = 'relatorio_consolidado_despesas_' . $dataInicial->format('Y-m-d') . '_' . $dataFinal->format('Y-m-d') . '.xls';
        $tempFile = ExcelExportService::criarExcel($dadosExcel, $filename, 'Relatório Consolidado de Despesas');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }

    public function exportarDetalhado(Request $request)
    {
        $filters = $request->all();
        $filters['data_inicial'] ??= Carbon::now()->startOfMonth()->format('d/m/Y');
        $filters['data_final'] ??= Carbon::now()->format('d/m/Y');
        $filters['categoria_id'] ??= '';

        $dataInicial = Carbon::createFromFormat('d/m/Y', $filters['data_inicial'])->startOfDay();
        $dataFinal = Carbon::createFromFormat('d/m/Y', $filters['data_final'])->endOfDay();

        $query = $this->model->whereBetween('data', [$dataInicial, $dataFinal]);

        if ($filters['categoria_id']) {
            $query->whereHas('despesaCategorias', function ($q) use ($filters) {
                $q->where('categoria_despesa_id', $filters['categoria_id']);
            });
        }

        $despesas = $query->with(['usuario', 'despesaCategorias.categoriaDespesa'])->get();

        // Preparar dados para Excel
        $dadosExcel = [];
        
        // Título e período
        $dadosExcel[] = ['Relatório Detalhado de Despesas'];
        $dadosExcel[] = ['Período: ' . $dataInicial->format('d/m/Y') . ' a ' . $dataFinal->format('d/m/Y')];
        $dadosExcel[] = [];
        
        // Cabeçalhos
        $dadosExcel[] = [
            'ID',
            'Número da Nota Fiscal',
            'Descrição',
            'Data',
            'Valor Total',
            'Categoria',
            'Valor Rateado',
            'Observações Rateio',
            'Cadastrado por',
            'Data de Cadastro'
        ];
        
        // Dados
        foreach ($despesas as $despesa) {
            if ($despesa->despesaCategorias->count() > 0) {
                foreach ($despesa->despesaCategorias as $index => $rateio) {
                    $dadosExcel[] = [
                        $index === 0 ? $despesa->id : '',
                        $index === 0 ? $despesa->numero_nota_fiscal : '',
                        $index === 0 ? $despesa->descricao : '',
                        $index === 0 ? $despesa->data->format('d/m/Y') : '',
                        $index === 0 ? number_format($despesa->valor_total, 2, ',', '.') : '',
                        $rateio->categoriaDespesa ? $rateio->categoriaDespesa->nome : 'Sem categoria',
                        number_format($rateio->valor, 2, ',', '.'),
                        $rateio->observacoes ?? '',
                        $index === 0 ? ($despesa->usuario->nome ?? '-') : '',
                        $index === 0 ? $despesa->created_at->format('d/m/Y H:i:s') : ''
                    ];
                }
            } else {
                // Despesa sem rateio
                $dadosExcel[] = [
                    $despesa->id,
                    $despesa->numero_nota_fiscal,
                    $despesa->descricao,
                    $despesa->data->format('d/m/Y'),
                    number_format($despesa->valor_total, 2, ',', '.'),
                    'Sem rateio',
                    '',
                    '',
                    $despesa->usuario->nome ?? '-',
                    $despesa->created_at->format('d/m/Y H:i:s')
                ];
            }
        }

        // Gerar arquivo Excel
        $filename = 'relatorio_detalhado_despesas_' . $dataInicial->format('Y-m-d') . '_' . $dataFinal->format('Y-m-d') . '.xls';
        $tempFile = ExcelExportService::criarExcel($dadosExcel, $filename, 'Relatório Detalhado de Despesas');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }
}

