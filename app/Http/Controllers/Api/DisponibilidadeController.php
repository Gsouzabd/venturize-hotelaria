<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Quarto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DisponibilidadeRequest;

class DisponibilidadeController extends Controller
{
    public function verificar(Request $request)
    {
        $dataEntrada = Carbon::createFromFormat('d/m/Y', $request->data_entrada)->format('Y-m-d');
        $dataSaida = Carbon::createFromFormat('d/m/Y', $request->data_saida)->format('Y-m-d');
        // Busca os quartos disponíveis com base no tipo de quarto, se fornecido
        $quartosQuery = Quarto::where('inativo', 0); // Apenas quartos que não estão inativos

        $quartosDisponiveis = $quartosQuery->whereDoesntHave('reservas', function ($query) use ($dataEntrada, $dataSaida) {
            $query->where(function ($query) use ($dataEntrada, $dataSaida) {
                // Verifica se a reserva está dentro do intervalo
                $query->whereBetween('data_checkin', [$dataEntrada, $dataSaida]);
                     
            })
            ->where('situacao_reserva', '!=', 'CANCELADA');
        })
        ->get();

        // Verifica se há quartos suficientes disponíveis
        if ($quartosDisponiveis->count() == 0) {
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