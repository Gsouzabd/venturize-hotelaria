<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaRefeicao extends Model
{
    protected $table = 'reserva_refeicoes';

    protected $fillable = [
        'reserva_id',
        'hospede_nome',
        'hospede_tipo',
        'acompanhante_id',
        'cafe',
        'almoco',
        'jantar',
    ];

    protected $casts = [
        'cafe'   => 'boolean',
        'almoco' => 'boolean',
        'jantar' => 'boolean',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}
