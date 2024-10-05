<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaQuartoOpcaoExtra extends Model
{
    protected $fillable = ['reserva_id', 'quarto_opcao_extra_id'];

    // Relação com Reserva
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    // Relação com QuartoOpcaoExtra
    public function quartoOpcaoExtra()
    {
        return $this->belongsTo(QuartoOpcaoExtra::class);
    }
}