<?php

namespace Database\Factories;

use App\Models\Reserva;
use App\Models\Quarto;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ReservaFactory extends Factory
{
    protected $model = Reserva::class;

    public function definition(): array
    {
        $checkin  = Carbon::now()->addDays(30)->format('Y-m-d');
        $checkout = Carbon::now()->addDays(33)->format('Y-m-d');

        return [
            'tipo_reserva'           => 'INDIVIDUAL',
            'tipo_solicitante'       => 'direto',
            'situacao_reserva'       => 'RESERVADO',
            'data_checkin'           => $checkin,
            'data_checkout'          => $checkout,
            'cliente_solicitante_id' => Cliente::factory(),
            'quarto_id'              => Quarto::factory(),
            'usuario_operador_id'    => 1,
            'adultos'                => 2,
            'criancas_ate_7'         => 0,
            'criancas_mais_7'        => 0,
            'total'                  => 300.00,
        ];
    }

    public function reservado(): static
    {
        return $this->state(['situacao_reserva' => 'RESERVADO']);
    }

    public function hospedado(): static
    {
        return $this->state(['situacao_reserva' => 'HOSPEDADO']);
    }

    public function cancelado(): static
    {
        return $this->state(['situacao_reserva' => 'CANCELADA']);
    }
}
