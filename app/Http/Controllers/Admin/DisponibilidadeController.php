<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\QuartoOpcaoExtra;
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
        $criancasAte7 = $validated['criancas_ate_7'];
        $criancasMais7 = $validated['criancas_mais_7'];

        // Busca os quartos disponíveis com base no tipo de quarto, se fornecido
        $quartosQuery = Quarto::where('inativo', 0); // Apenas quartos que não estão inativos
        if ($tipoQuarto) {
            $quartosQuery->where('classificacao', $tipoQuarto);
        }

        $quartosDisponiveis = $quartosQuery->whereDoesntHave('reservas', function ($query) use ($dataEntrada, $dataSaida) {
            $query->where(function ($query) use ($dataEntrada, $dataSaida) {
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
            })
            ->where('situacao_reserva', '!=', 'CANCELADA');

        })
        ->with('planosPrecos')
        ->get();

        // Filtrar os planos de preços para cada quarto
        $quartosDisponiveis->each(function ($quarto) use ($dataEntrada, $dataSaida) {
            $quarto->planoPreco = $this->obterPlanoPreco($quarto, $dataEntrada, $dataSaida);
        });

        // Adicionar preços diários com base no dia da semana e opções extras de crianças
        $quartosDisponiveis->each(function ($quarto) use ($dataEntrada, $dataSaida, $criancasAte7, $criancasMais7) {
            $quarto->precosDiarios = $this->calcularPrecosDiarios($quarto->planoPreco, $dataEntrada, $dataSaida, $criancasAte7, $criancasMais7);
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
        // Ajustar para lidar com diferentes formatos de data
        try {
            $dataEntrada = $this->parseDate($request->input('data_entrada'));
            $dataSaida = $this->parseDate($request->input('data_saida'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        $quarto = Quarto::with('planosPrecos')->findOrFail($quartoId);
        $planoPreco = $this->obterPlanoPreco($quarto, $dataEntrada, $dataSaida);
        $precosDiarios = $this->calcularPrecosDiarios($planoPreco, $dataEntrada, $dataSaida, $request->input('criancas_ate_7'), $request->input('criancas_mais_7'));

        return response()->json([
            'success' => true,
            'precosDiarios' => $precosDiarios,
            'total' => number_format(array_sum($precosDiarios), 2, '.', '')
        ]);
    }

    private function obterPlanoPreco($quarto, $dataEntrada, $dataSaida, $criancasAte7 = 0, $criancasMais7 = 0)
    {
        $planoPreco = $quarto->planosPrecos->filter(function ($plano) use ($dataEntrada, $dataSaida) {
            return $plano->data_inicio <= $dataEntrada && $plano->data_fim >= $dataSaida && $plano->is_default == 0;
        })->first();

        if (!$planoPreco) {
            $planoPreco = $quarto->planosPrecos->where('is_default', 1)->first();
        }

        return $planoPreco;
    }

    private function calcularPrecosDiarios($planoPreco, $dataEntrada, $dataSaida, $criancasAte7, $criancasMais7)
    {
        $dataAtual = Carbon::parse($dataEntrada);
        $dataFim = Carbon::parse($dataSaida);
        $precosDiarios = [];

        // Obtém os preços das opções extras de crianças
        $precoCriancaAte7 = QuartoOpcaoExtra::where('nome', 'Criança (Até 7 anos)')->value('preco');
        $precoCriancaMais7 = QuartoOpcaoExtra::where('nome', 'Criança (07 à 12 anos)')->value('preco');

        $dataFim = $dataFim->subDay(); // Subtrai um dia para não incluir o dia de check-out

        while ($dataAtual->lte($dataFim)) {
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

            // Adiciona os preços das opções extras de crianças
            $precoDia += $this->calcularPrecoCriancas($criancasAte7, $precoCriancaAte7) + ($precoCriancaMais7 * $criancasMais7);

            $precosDiarios[$dataAtual->toDateString()] = $precoDia;
            $dataAtual->addDay();
        }

        return $precosDiarios;
    }

    private function calcularPrecoCriancas($criancasAte7, $precoCriancaAte7)
    {
        $precoTotal = 0;

        if ($criancasAte7 > 0) {
            $precoTotal += 0; // Preço da primeira criança é 0
        }

        if ($criancasAte7 > 1) {
            $precoTotal += $precoCriancaAte7; // Preço da segunda criança
        }

        return $precoTotal;
    }

    private function parseDate($dateString)
    {
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
}