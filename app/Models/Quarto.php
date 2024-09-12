<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quarto extends Model
{
    protected $fillable = [
        'andar', 'numero', 'ramal', 'posicao_quarto', 
        'quantidade_cama_casal', 'quantidade_cama_solteiro', 'classificacao',
        'acessibilidade', 'inativo'
    ];

    // Relação com Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}
