<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Despesa;
use App\Models\CategoriaDespesa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DespesaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            
            $query = Despesa::with(['usuario', 'despesaCategorias.categoriaDespesa']);

            // Filtro por número da nota fiscal
            if ($request->has('numero_nota_fiscal')) {
                $query->where('numero_nota_fiscal', 'like', '%' . $request->numero_nota_fiscal . '%');
            }

            // Filtro por período
            if ($request->has('data_inicial') && $request->has('data_final')) {
                $dataInicial = Carbon::parse($request->data_inicial)->startOfDay();
                $dataFinal = Carbon::parse($request->data_final)->endOfDay();
                $query->whereBetween('data', [$dataInicial, $dataFinal]);
            } elseif ($request->has('data_inicial')) {
                $dataInicial = Carbon::parse($request->data_inicial)->startOfDay();
                $query->where('data', '>=', $dataInicial);
            } elseif ($request->has('data_final')) {
                $dataFinal = Carbon::parse($request->data_final)->endOfDay();
                $query->where('data', '<=', $dataFinal);
            }

            // Filtro por categoria
            if ($request->has('categoria_id')) {
                $query->whereHas('despesaCategorias', function ($q) use ($request) {
                    $q->where('categoria_despesa_id', $request->categoria_id);
                });
            }

            $despesas = $query->orderBy('data', 'desc')->orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $despesas->map(function ($despesa) {
                    return [
                        'id' => $despesa->id,
                        'numero_nota_fiscal' => $despesa->numero_nota_fiscal,
                        'descricao' => $despesa->descricao,
                        'data' => $despesa->data->format('Y-m-d'),
                        'valor_total' => (float) $despesa->valor_total,
                        'arquivo_nota' => $despesa->arquivo_nota ? asset('storage/' . $despesa->arquivo_nota) : null,
                        'observacoes' => $despesa->observacoes,
                        'usuario' => $despesa->usuario ? [
                            'id' => $despesa->usuario->id,
                            'nome' => $despesa->usuario->nome,
                        ] : null,
                        'rateios' => $despesa->despesaCategorias->map(function ($rateio) {
                            return [
                                'id' => $rateio->id,
                                'categoria' => $rateio->categoriaDespesa ? [
                                    'id' => $rateio->categoriaDespesa->id,
                                    'nome' => $rateio->categoriaDespesa->nome,
                                ] : null,
                                'valor' => (float) $rateio->valor,
                                'observacoes' => $rateio->observacoes,
                            ];
                        }),
                        'created_at' => $despesa->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $despesa->updated_at->format('Y-m-d H:i:s'),
                    ];
                }),
                'count' => $despesas->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar despesas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function relatorio(Request $request): JsonResponse
    {
        try {
            $dataInicial = $request->has('data_inicial') 
                ? Carbon::parse($request->data_inicial)->startOfDay() 
                : Carbon::now()->startOfMonth()->startOfDay();
            
            $dataFinal = $request->has('data_final') 
                ? Carbon::parse($request->data_final)->endOfDay() 
                : Carbon::now()->endOfDay();

            $query = Despesa::whereBetween('data', [$dataInicial, $dataFinal]);

            // Filtro por categoria
            if ($request->has('categoria_id')) {
                $query->whereHas('despesaCategorias', function ($q) use ($request) {
                    $q->where('categoria_despesa_id', $request->categoria_id);
                });
            }

            $despesas = $query->with('despesaCategorias.categoriaDespesa')->get();

            // Consolidar por categoria
            $consolidado = [];
            $totalGeral = 0;
            $totalDespesas = 0;

            foreach ($despesas as $despesa) {
                $totalDespesas++;
                
                foreach ($despesa->despesaCategorias as $despesaCategoria) {
                    $categoriaNome = $despesaCategoria->categoriaDespesa 
                        ? $despesaCategoria->categoriaDespesa->nome 
                        : 'Sem categoria';
                    $categoriaId = $despesaCategoria->categoria_despesa_id ?? 'sem_categoria';
                    
                    if (!isset($consolidado[$categoriaId])) {
                        $consolidado[$categoriaId] = [
                            'id' => $categoriaId,
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

            // Converter array associativo para array indexado
            $consolidadoArray = array_values($consolidado);

            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => [
                        'data_inicial' => $dataInicial->format('Y-m-d'),
                        'data_final' => $dataFinal->format('Y-m-d'),
                    ],
                    'total_geral' => (float) $totalGeral,
                    'total_despesas' => $totalDespesas,
                    'consolidado_por_categoria' => $consolidadoArray,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function categorias(): JsonResponse
    {
        try {
            $categorias = CategoriaDespesa::ativas()->orderBy('nome')->get();

            return response()->json([
                'success' => true,
                'data' => $categorias->map(function ($categoria) {
                    return [
                        'id' => $categoria->id,
                        'nome' => $categoria->nome,
                        'descricao' => $categoria->descricao,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar categorias: ' . $e->getMessage(),
            ], 500);
        }
    }
}

