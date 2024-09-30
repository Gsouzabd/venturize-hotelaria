<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DisponibilidadeRequest;

class DisponibilidadeController extends Controller
{
    public function verificar(DisponibilidadeRequest $request)
    {
        // Obtém os parâmetros validados da requisição
        $validated = $request->validated();
        $dataEntrada = Carbon::createFromFormat('d/m/Y', $validated['data_entrada'])->format('Y-m-d');
        $dataSaida = Carbon::createFromFormat('d/m/Y', $validated['data_saida'])->format('Y-m-d');
        $tipoQuarto = $request['tipo_quarto'] ?? null;
        $apartamentosNecessarios = $validated['apartamentos'];

        // Busca os quartos disponíveis com base no tipo de quarto, se fornecido
        $quartosQuery = Quarto::where('inativo', 0); // Apenas quartos que não estão inativos
        if ($tipoQuarto) {
            $quartosQuery->where('classificacao', $tipoQuarto);
        }

        $quartosDisponiveis = $quartosQuery->whereDoesntHave('reservas', function ($query) use ($dataEntrada, $dataSaida, $tipoQuarto) {
            $query->where(function ($query) use ($dataEntrada, $dataSaida, $tipoQuarto) {
                // Verifica se a reserva está dentro do intervalo
                $query->whereBetween('data_checkin', [$dataEntrada, $dataSaida])
                      ->orWhereBetween('data_checkout', [$dataEntrada, $dataSaida])
                      // Verifica se a reserva cobre todo o período
                      ->orWhere(function ($query) use ($dataEntrada, $dataSaida) {
                          $query->where('data_checkin', '<', $dataEntrada)
                                ->where('data_checkout', '>', $dataSaida);
                      })
                      // Verifica se o check-out termina no $dataEntrada
                      ->orWhere(function ($query) use ($dataEntrada) {
                          $query->where('data_checkout', $dataEntrada);
                      })
                      // Verifica se o check-in começa no $dataSaida
                      ->orWhere(function ($query) use ($dataSaida) {
                          $query->where('data_checkin', $dataSaida);
                      });
            });
        })
        ->with('planosPrecos')
        ->get();

        // Filtrar os planos de preços para cada quarto
        $quartosDisponiveis->each(function ($quarto) use ($dataEntrada, $dataSaida) {
            $planoPreco = $quarto->planosPrecos->filter(function ($plano) use ($dataEntrada, $dataSaida) {
                return $plano->data_inicio <= $dataEntrada && $plano->data_fim >= $dataSaida && $plano->is_default == 0;
            })->first();

            if (!$planoPreco) {
                $planoPreco = $quarto->planosPrecos->where('is_default', 1)->first();
            }

            $quarto->planoPreco = $planoPreco;
        });

        // Adicionar preços diários com base no dia da semana
        $quartosDisponiveis->each(function ($quarto) use ($dataEntrada, $dataSaida) {
            $dataAtual = Carbon::parse($dataEntrada);
            $dataFim = Carbon::parse($dataSaida);
            $precosDiarios = [];

            while ($dataAtual->lte($dataFim)) {
                $diaSemana = $dataAtual->format('l'); // Obtém o dia da semana em inglês
                $precoDia = null;

                switch ($diaSemana) {
                    case 'Sunday':
                        $precoDia = $quarto->planoPreco->preco_domingo;
                        break;
                    case 'Monday':
                        $precoDia = $quarto->planoPreco->preco_segunda;
                        break;
                    case 'Tuesday':
                        $precoDia = $quarto->planoPreco->preco_terca;
                        break;
                    case 'Wednesday':
                        $precoDia = $quarto->planoPreco->preco_quarta;
                        break;
                    case 'Thursday':
                        $precoDia = $quarto->planoPreco->preco_quinta;
                        break;
                    case 'Friday':
                        $precoDia = $quarto->planoPreco->preco_sexta;
                        break;
                    case 'Saturday':
                        $precoDia = $quarto->planoPreco->preco_sabado;
                        break;
                }

                $precosDiarios[$dataAtual->toDateString()] = $precoDia;
                $dataAtual->addDay();
            }

            $quarto->precosDiarios = $precosDiarios;
        });

        // Verifica se há quartos suficientes disponíveis
        if ($quartosDisponiveis->count() < $apartamentosNecessarios) {
            return response()->json([
                'success' => false,
                'message' => 'Não há quartos disponíveis suficientes para as datas selecionadas.'
            ]);
        }

        // Retorna os quartos disponíveis como JSON
        return response()->json([
            'success' => true,
            'quartos' => $quartosDisponiveis
        ]);
    }

    public function obterPlanosPrecos(Request $request, $quartoId)
    {
        // Remover a linha de debug
        // dd($request->all());
    
        // Ajustar para lidar com o formato de data e hora
        function parseDate($dateString) {
            $formats = ['Y-m-d H:i:s', 'd/m/Y', 'd-m-Y'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateString)->format('Y-m-d');
                } catch (\Exception $e) {
                    // Continua tentando o próximo formato
                }
            }
            throw new \Exception("Formato de data inválido: $dateString");
        }
    
        // Ajustar para lidar com diferentes formatos de data
        try {
            $dataEntrada = parseDate($request->input('data_entrada'));
            $dataSaida = parseDate($request->input('data_saida'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        $quarto = Quarto::with('planosPrecos')->findOrFail($quartoId);
    
        $planoPreco = $quarto->planosPrecos->filter(function ($plano) use ($dataEntrada, $dataSaida) {
            return $plano->data_inicio <= $dataEntrada && $plano->data_fim >= $dataSaida && $plano->is_default == 0;
        })->first();
    
        if (!$planoPreco) {
            $planoPreco = $quarto->planosPrecos->where('is_default', 1)->first();
        }
    
        $dataAtual = Carbon::parse($dataEntrada);
        $dataFim = Carbon::parse($dataSaida);
        $precosDiarios = [];
    
        while ($dataAtual->lt($dataFim)) { // Usar 'lt' para estritamente menor
            $diaSemana = $dataAtual->format('l'); // Obtém o dia da semana em inglês
            $precoDia = null;
        
            switch ($diaSemana) {
                case 'Sunday':
                    $precoDia = $planoPreco->preco_domingo;
                    break;
                case 'Monday':
                    $precoDia = $planoPreco->preco_segunda;
                    break;
                case 'Tuesday':
                    $precoDia = $planoPreco->preco_terca;
                    break;
                case 'Wednesday':
                    $precoDia = $planoPreco->preco_quarta;
                    break;
                case 'Thursday':
                    $precoDia = $planoPreco->preco_quinta;
                    break;
                case 'Friday':
                    $precoDia = $planoPreco->preco_sexta;
                    break;
                case 'Saturday':
                    $precoDia = $planoPreco->preco_sabado;
                    break;
            }
        
            $precosDiarios[$dataAtual->toDateString()] = $precoDia;
            $dataAtual->addDay();
        }
    
        return response()->json([
            'success' => true,
            'precosDiarios' => $precosDiarios,
            'total' => number_format(array_sum($precosDiarios), 2, '.', '')        
        ]);
    }
}