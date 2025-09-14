<?php

namespace App\Http\Controllers\Api;

use App\Models\Bar\Pedido;
use App\Models\Bar\ImpressaoPedido;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PrintController extends Controller
{
    /**
     * Verifica o status de impressão de um pedido
     *
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function verificarStatusImpressao(int $pedidoId): JsonResponse
    {
        try {
            $pedido = Pedido::with(['impressoes'])->findOrFail($pedidoId);
            
            $foiImpresso = $pedido->foiImpresso();
             $temPendente = $pedido->temImpressaoPendente();
             $ultimaImpressao = $pedido->ultimaImpressao;
             
             return response()->json([
                 'success' => true,
                 'data' => [
                     'pedido_id' => $pedidoId,
                     'foi_impresso' => $foiImpresso,
                     'tem_impressao_pendente' => $temPendente,
                     'pode_imprimir_diretamente' => !$foiImpresso && !$temPendente,
                     'requer_confirmacao' => $foiImpresso || $temPendente,
                     'ultima_impressao' => $ultimaImpressao ? [
                         'id' => $ultimaImpressao->id,
                         'status' => $ultimaImpressao->status_impressao,
                         'agente' => $ultimaImpressao->agente_impressao,
                         'data' => $ultimaImpressao->created_at->format('d/m/Y H:i:s')
                     ] : null,
                     'total_impressoes' => $pedido->totalImpressoes()
                 ],
                 'message' => 'Status de impressão verificado com sucesso'
             ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status de impressão: ' . $e->getMessage(),
                'error_code' => 'CHECK_PRINT_STATUS_ERROR'
            ], 500);
        }
    }

    /**
     * Gera dados do pedido em formato JSON para o agente de impressão
     * VERSÃO ATUALIZADA - 2024-12-07
     *
     * @param int $pedidoId
     * @param Request $request
     * @return JsonResponse
     */
    public function getPedidoForPrint(int $pedidoId, Request $request): JsonResponse
    {
        try {
            // Buscar o pedido com todos os relacionamentos necessários
            $pedido = Pedido::with([
                'mesa',
                'cliente',
                'reserva.quarto',
                'itens.produto.categoria',
                'itens.operador',
                'impressoes'
            ])->findOrFail($pedidoId);
            
            // Verificar status de impressão
             $foiImpresso = $pedido->foiImpresso();
             $temPendente = $pedido->temImpressaoPendente();
             $forcarImpressao = $request->input('forcar_impressao', false);
             $impressaoId = $request->input('impressao_id'); // ID específico da impressão para atualizar
            
            // Se já foi impresso (mas não pendente) e não está forçando, retornar aviso
             if ($foiImpresso && !$temPendente && !$forcarImpressao) {
                 $ultimaImpressao = $pedido->ultimaImpressao;
                 
                 return response()->json([
                     'success' => false,
                     'requires_confirmation' => true,
                     'data' => [
                         'pedido_id' => $pedidoId,
                         'foi_impresso' => $foiImpresso,
                         'tem_impressao_pendente' => $temPendente,
                         'ultima_impressao' => $ultimaImpressao ? [
                             'status' => $ultimaImpressao->status_impressao,
                             'agente' => $ultimaImpressao->agente_impressao,
                             'data' => $ultimaImpressao->created_at->format('d/m/Y H:i:s')
                         ] : null,
                         'total_impressoes' => $pedido->totalImpressoes()
                     ],
                     'message' => 'Este pedido já foi impresso anteriormente. Deseja imprimir novamente?',
                     'error_code' => 'ALREADY_PRINTED'
                 ], 409); // 409 Conflict
             }
             
             // API processa pedidos pendentes sem crítica (agente lista e imprime)
             // Críticas devem ser feitas apenas na interface web, não na API
            
            // Gerenciar registro de impressão: atualizar específico, pendente existente ou criar novo
             $impressaoAtual = null;
              if ($impressaoId) {
                  // Atualizar impressão específica pelo ID fornecido
                  $impressaoEspecifica = $pedido->impressoes()->find($impressaoId);
                  if ($impressaoEspecifica) {
                      $impressaoEspecifica->update([
                          'agente_impressao' => $request->input('agente', 'sistema_web'),
                          'ip_origem' => $request->ip(),
                          'dados_impressao' => array_merge($impressaoEspecifica->dados_impressao ?? [], [
                              'user_agent' => $request->userAgent(),
                              'timestamp_ultima_solicitacao' => now()->toISOString(),
                              'tipo_solicitacao' => 'cupom_parcial'
                          ]),
                          'updated_at' => now()
                      ]);
                      $impressaoAtual = $impressaoEspecifica;
                  }
              } else if ($temPendente) {
                  // Atualizar registro pendente existente com novos dados da solicitação
                  $impressaoPendente = $pedido->impressoes()->where('status_impressao', 'pendente')->first();
                  if ($impressaoPendente) {
                      $impressaoPendente->update([
                          'agente_impressao' => $request->input('agente', 'sistema_web'),
                          'ip_origem' => $request->ip(),
                          'dados_impressao' => array_merge($impressaoPendente->dados_impressao ?? [], [
                              'user_agent' => $request->userAgent(),
                              'timestamp_ultima_solicitacao' => now()->toISOString(),
                              'tipo_solicitacao' => 'cupom_parcial'
                          ]),
                          'updated_at' => now()
                      ]);
                      $impressaoAtual = $impressaoPendente;
                  }
              } else if ($forcarImpressao || (!$foiImpresso && !$temPendente)) {
                  // Criar novo registro de impressão pendente apenas se não existir
                  $impressaoAtual = $pedido->impressoes()->create([
                      'agente_impressao' => $request->input('agente', 'sistema_web'),
                      'ip_origem' => $request->ip(),
                      'status_impressao' => 'pendente',
                      'dados_impressao' => [
                          'user_agent' => $request->userAgent(),
                          'timestamp_solicitacao' => now()->toISOString(),
                          'tipo_solicitacao' => 'cupom_parcial'
                      ]
                  ]);
              }

            // Estruturar os dados para impressão
            $dadosImpressao = [
                'pedido' => [
                    'id' => $pedido->id,
                    'status' => $pedido->status,
                    'total' => $pedido->total,
                    'taxa_servico' => $pedido->taxa_servico,
                    'total_com_taxa' => $pedido->total_com_taxa,
                    'pedido_apartamento' => $pedido->pedido_apartamento,
                    'observacoes' => $pedido->observacoes,
                    'created_at' => $pedido->created_at->format('d/m/Y H:i:s'),
                    'updated_at' => $pedido->updated_at->format('d/m/Y H:i:s')
                ],
                'mesa' => $pedido->mesa ? [
                    'numero' => $pedido->mesa->numero,
                    'status' => $pedido->mesa->status
                ] : null,
                'cliente' => [
                    'id' => $pedido->cliente->id,
                    'nome' => $pedido->cliente->nome,
                    'cpf' => $pedido->cliente->cpf ?? null,
                    'telefone' => $pedido->cliente->telefone ?? null
                ],
                'reserva' => $pedido->reserva ? [
                    'id' => $pedido->reserva->id,
                    'quarto' => [
                        'numero' => $pedido->reserva->quarto->numero ?? null,
                        'tipo' => $pedido->reserva->quarto->tipo ?? null
                    ]
                ] : null,
                'itens' => $pedido->itens->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'quantidade' => $item->quantidade,
                        'preco' => $item->preco,
                        'subtotal' => $item->quantidade * $item->preco,
                        'produto' => [
                            'id' => $item->produto->id,
                            'descricao' => $item->produto->descricao,
                            'categoria' => $item->produto->categoria->nome ?? null
                        ],
                        'operador' => [
                            'id' => $item->operador->id ?? null,
                            'nome' => $item->operador->nome ?? null
                        ],
                        'created_at' => $item->created_at->format('d/m/Y H:i:s')
                    ];
                })->toArray(),
                'empresa' => [
                    'nome' => config('app.name', 'Venturize Hotelaria'),
                    'endereco' => 'Endereço da empresa', // Pode ser configurado
                    'telefone' => 'Telefone da empresa', // Pode ser configurado
                    'cnpj' => 'CNPJ da empresa' // Pode ser configurado
                ],
                'timestamp_impressao' => now()->format('d/m/Y H:i:s'),
                'tipo_cupom' => 'parcial'
            ];

            return response()->json([
                'success' => true,
                'data' => $dadosImpressao,
                'impressao_id' => $impressaoAtual ? $impressaoAtual->id : null,
                'message' => 'Dados do pedido gerados com sucesso para impressão'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar dados para impressão: ' . $e->getMessage(),
                'error_code' => 'PRINT_DATA_ERROR'
            ], 500);
        }
    }

    /**
     * Lista pedidos pendentes de impressão
     *
     * @return JsonResponse
     */
    public function getPedidosPendentes(): JsonResponse
    {
        try {
            // Buscar pedidos abertos que têm impressões pendentes
            $pedidosPendentes = Pedido::with(['mesa', 'cliente', 'reserva.quarto', 'ultimaImpressao'])
                ->where('status', 'aberto')
                ->whereHas('itens')
                ->whereHas('impressoes', function($query) {
                    $query->where('status_impressao', 'pendente');
                })
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($pedido) {
                    return [
                        'id' => $pedido->id,
                        'mesa_numero' => $pedido->mesa->numero ?? null,
                        'cliente_nome' => $pedido->cliente->nome,
                        'quarto_numero' => $pedido->reserva->quarto->numero ?? null,
                        'total' => $pedido->total,
                        'pedido_apartamento' => $pedido->pedido_apartamento,
                        'updated_at' => $pedido->updated_at->format('d/m/Y H:i:s'),
                        'itens_count' => $pedido->itens->count(),
                        'tem_impressao_pendente' => $pedido->temImpressaoPendente(),
                        'ultima_tentativa_impressao' => $pedido->ultimaImpressao ? 
                            $pedido->ultimaImpressao->created_at->format('d/m/Y H:i:s') : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $pedidosPendentes,
                'count' => $pedidosPendentes->count(),
                'message' => 'Lista de pedidos pendentes obtida com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter pedidos pendentes: ' . $e->getMessage(),
                'error_code' => 'PENDING_ORDERS_ERROR'
            ], 500);
        }
    }

    /**
     * Marca um pedido como impresso (para controle do agente)
     *
     * @param Request $request
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function marcarComoImpresso(Request $request, int $pedidoId): JsonResponse
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            $impressaoId = $request->input('impressao_id');
            
            if ($impressaoId) {
                // Atualizar registro específico existente
                $impressao = $pedido->impressoes()->find($impressaoId);
                if (!$impressao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registro de impressão não encontrado',
                        'error_code' => 'IMPRESSAO_NOT_FOUND'
                    ], 404);
                }
                
                $impressao->update([
                    'agente_impressao' => $request->input('agente', 'agente_externo'),
                    'ip_origem' => $request->ip(),
                    'status_impressao' => 'sucesso',
                    'dados_impressao' => array_merge($impressao->dados_impressao ?? [], [
                        'user_agent' => $request->userAgent(),
                        'timestamp_processamento' => now()->toISOString(),
                        'impressora_usada' => $request->input('impressora'),
                        'metodo_impressao' => $request->input('metodo', 'api')
                    ])
                ]);
            } else {
                // Buscar registro em processamento ou pendente para atualizar
                $impressao = $pedido->impressoes()
                    ->whereIn('status_impressao', ['processando', 'pendente'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($impressao) {
                    // Atualizar registro existente para sucesso
                    $impressao->update([
                        'agente_impressao' => $request->input('agente', 'agente_externo'),
                        'ip_origem' => $request->ip(),
                        'status_impressao' => 'sucesso',
                        'dados_impressao' => array_merge($impressao->dados_impressao ?? [], [
                            'user_agent' => $request->userAgent(),
                            'timestamp_processamento' => now()->toISOString(),
                            'impressora_usada' => $request->input('impressora'),
                            'metodo_impressao' => $request->input('metodo', 'api')
                        ])
                    ]);
                } else {
                    // Criar novo registro se não houver em processamento/pendente
                    $impressao = $pedido->impressoes()->create([
                        'agente_impressao' => $request->input('agente', 'agente_externo'),
                        'ip_origem' => $request->ip(),
                        'status_impressao' => 'sucesso',
                        'dados_impressao' => [
                            'user_agent' => $request->userAgent(),
                            'timestamp_processamento' => now()->toISOString(),
                            'impressora_usada' => $request->input('impressora'),
                            'metodo_impressao' => $request->input('metodo', 'api')
                        ]
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido marcado como impresso com sucesso',
                'pedido_id' => $pedidoId,
                'impressao_id' => $impressao->id,
                'timestamp' => $impressao->created_at->format('d/m/Y H:i:s'),
                'total_impressoes' => $pedido->totalImpressoes()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar pedido como impresso: ' . $e->getMessage(),
                'error_code' => 'MARK_PRINTED_ERROR'
            ], 500);
        }
    }

    /**
     * Registra tentativa de impressão (quando o agente inicia o processo)
     *
     * @param Request $request
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function registrarTentativaImpressao(Request $request, int $pedidoId): JsonResponse
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            $impressaoId = $request->input('impressao_id');
            
            if ($impressaoId) {
                // Atualizar registro específico existente
                $impressao = $pedido->impressoes()->find($impressaoId);
                if (!$impressao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Registro de impressão não encontrado',
                        'error_code' => 'IMPRESSAO_NOT_FOUND'
                    ], 404);
                }
                
                $impressao->update([
                    'agente_impressao' => $request->input('agente', 'agente_externo'),
                    'ip_origem' => $request->ip(),
                    'status_impressao' => 'processando',
                    'dados_impressao' => array_merge($impressao->dados_impressao ?? [], [
                        'user_agent' => $request->userAgent(),
                        'timestamp_inicio' => now()->toISOString(),
                        'impressora_alvo' => $request->input('impressora')
                    ])
                ]);
            } else {
                // Buscar registro pendente existente ou criar novo
                $impressao = $pedido->impressoes()->where('status_impressao', 'pendente')->first();
                
                if ($impressao) {
                    // Atualizar registro pendente para processando
                    $impressao->update([
                        'agente_impressao' => $request->input('agente', 'agente_externo'),
                        'ip_origem' => $request->ip(),
                        'status_impressao' => 'processando',
                        'dados_impressao' => array_merge($impressao->dados_impressao ?? [], [
                            'user_agent' => $request->userAgent(),
                            'timestamp_inicio' => now()->toISOString(),
                            'impressora_alvo' => $request->input('impressora')
                        ])
                    ]);
                } else {
                    // Criar novo registro se não houver pendente
                    $impressao = $pedido->impressoes()->create([
                        'agente_impressao' => $request->input('agente', 'agente_externo'),
                        'ip_origem' => $request->ip(),
                        'status_impressao' => 'processando',
                        'dados_impressao' => [
                            'user_agent' => $request->userAgent(),
                            'timestamp_inicio' => now()->toISOString(),
                            'impressora_alvo' => $request->input('impressora')
                        ]
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Tentativa de impressão registrada',
                'pedido_id' => $pedidoId,
                'impressao_id' => $impressao->id,
                'timestamp' => $impressao->updated_at->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar tentativa: ' . $e->getMessage(),
                'error_code' => 'REGISTER_ATTEMPT_ERROR'
            ], 500);
        }
    }

    /**
     * Marca uma impressão como erro
     *
     * @param Request $request
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function marcarErroImpressao(Request $request, int $pedidoId): JsonResponse
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            $impressaoId = $request->input('impressao_id');
            
            if ($impressaoId) {
                // Atualizar impressão específica
                $impressao = $pedido->impressoes()->findOrFail($impressaoId);
                $impressao->marcarComoErro(
                    $request->input('erro', 'Erro não especificado'),
                    [
                        'timestamp_erro' => now()->toISOString(),
                        'detalhes_tecnicos' => $request->input('detalhes_tecnicos')
                    ]
                );
            } else {
                // Criar novo registro de erro
                $impressao = $pedido->impressoes()->create([
                    'agente_impressao' => $request->input('agente', 'agente_externo'),
                    'ip_origem' => $request->ip(),
                    'status_impressao' => 'erro',
                    'detalhes_erro' => $request->input('erro', 'Erro não especificado'),
                    'dados_impressao' => [
                        'user_agent' => $request->userAgent(),
                        'timestamp_erro' => now()->toISOString(),
                        'detalhes_tecnicos' => $request->input('detalhes_tecnicos')
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Erro de impressão registrado',
                'pedido_id' => $pedidoId,
                'impressao_id' => $impressao->id,
                'timestamp' => $impressao->updated_at->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar falha: ' . $e->getMessage(),
                'error_code' => 'REGISTER_ERROR_ERROR'
            ], 500);
        }
    }

    /**
     * Obtém histórico de impressões de um pedido
     *
     * @param int $pedidoId
     * @return JsonResponse
     */
    public function getHistoricoImpressoes(int $pedidoId): JsonResponse
    {
        try {
            $pedido = Pedido::with('impressoes')->findOrFail($pedidoId);
            
            $historico = $pedido->impressoes->map(function($impressao) {
                return [
                    'id' => $impressao->id,
                    'agente' => $impressao->agente_impressao,
                    'status' => $impressao->status_impressao,
                    'ip_origem' => $impressao->ip_origem,
                    'detalhes_erro' => $impressao->detalhes_erro,
                    'dados_impressao' => $impressao->dados_impressao,
                    'created_at' => $impressao->created_at->format('d/m/Y H:i:s'),
                    'updated_at' => $impressao->updated_at->format('d/m/Y H:i:s')
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $historico,
                'total_impressoes' => $historico->count(),
                'impressoes_sucesso' => $historico->where('status', 'sucesso')->count(),
                'impressoes_erro' => $historico->where('status', 'erro')->count(),
                'foi_impresso' => $pedido->foiImpresso()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter histórico: ' . $e->getMessage(),
                'error_code' => 'GET_HISTORY_ERROR'
            ], 500);
        }
    }

    /**
     * Obtém estatísticas de impressão
     *
     * @return JsonResponse
     */
    public function getEstatisticasImpressao(): JsonResponse
    {
        try {
            $stats = [
                'pedidos_pendentes' => Pedido::where('status', 'aberto')
                    ->whereHas('itens')
                    ->naoImpressos()
                    ->count(),
                'pedidos_impressos_hoje' => ImpressaoPedido::sucesso()
                    ->hoje()
                    ->distinct('pedido_id')
                    ->count(),
                'total_impressoes_hoje' => ImpressaoPedido::hoje()->count(),
                'impressoes_com_erro_hoje' => ImpressaoPedido::comErro()->hoje()->count(),
                'agentes_ativos' => ImpressaoPedido::hoje()
                    ->distinct('agente_impressao')
                    ->pluck('agente_impressao')
                    ->toArray()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage(),
                'error_code' => 'GET_STATS_ERROR'
            ], 500);
        }
    }
}