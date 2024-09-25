<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Quarto;
use App\Models\Reserva;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DisponibilidadeRequest;

class DisponibilidadeController extends Controller
{
    public function verificar(DisponibilidadeRequest $request)
    {
        // Obtém os parâmetros validados da requisição
        $validated = $request->validated();
        $dataEntrada = Carbon::createFromFormat('d/m/Y', $validated['data_entrada'])->format('Y/m/d');
        $dataSaida = Carbon::createFromFormat('d/m/Y', $validated['data_saida'])->format('Y/m/d');
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
                          $query->where('data_checkin', '<=', $dataEntrada)
                                ->where('data_checkout', '>=', $dataSaida);
                      });
            });
        })
        ->get();

        // dd query e parametros
        // dd($quartosQuery->toSql(), $quartosQuery->getBindings());

        
        

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
}