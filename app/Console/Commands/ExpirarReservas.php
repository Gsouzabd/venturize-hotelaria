<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Reserva;
use Illuminate\Console\Command;

class ExpirarReservas extends Command
{
    protected $signature = 'reservas:expirar';
    protected $description = 'Marca como NO SHOW reservas sem check-in após o horário de checkout (12:00)';

    public function handle()
    {
        $agora = Carbon::now('America/Sao_Paulo');

        // Reservas normais: expiram após o horário de checkout (12:00)
        $expiradas = Reserva::whereIn('situacao_reserva', ['RESERVADO', 'PRÉ RESERVA'])
            ->where(fn ($q) => $q->whereNull('tipo_reserva')->orWhere('tipo_reserva', '!=', 'DAY_USE'))
            ->where('data_checkout', '<=', $agora)
            ->update(['situacao_reserva' => 'NO SHOW']);

        // Day Use: o data_checkout é normalizado para 12:00 do próprio dia,
        // então só expira a partir do dia seguinte para não marcar NO SHOW
        // um day use vespertino que ainda vai acontecer
        $expiradas += Reserva::whereIn('situacao_reserva', ['RESERVADO', 'PRÉ RESERVA'])
            ->where('tipo_reserva', 'DAY_USE')
            ->whereDate('data_checkout', '<', $agora->toDateString())
            ->update(['situacao_reserva' => 'NO SHOW']);

        $this->info("{$expiradas} reserva(s) marcada(s) como NO SHOW.");

        return 0;
    }
}
