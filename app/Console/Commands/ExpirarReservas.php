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

        $expiradas = Reserva::whereIn('situacao_reserva', ['RESERVADO', 'PRÉ RESERVA'])
            ->where('data_checkout', '<=', $agora)
            ->update(['situacao_reserva' => 'NO SHOW']);

        $this->info("{$expiradas} reserva(s) marcada(s) como NO SHOW.");

        return 0;
    }
}
