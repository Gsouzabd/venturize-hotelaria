<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quarto extends Model
{
    protected $fillable = [
        'andar', 'numero', 'ramal', 'posicao_quarto', 'referencia',
        'quantidade_cama_casal', 'quantidade_cama_solteiro', 'classificacao',
        'acessibilidade', 'inativo'
    ];

    // Relação com Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    // Relação com QuartoPlanoPreco
    public function planosPrecos()
    {
        return $this->hasMany(QuartoPlanoPreco::class, 'quarto_id');
    
    }

    // Método para obter o plano de preço atual
    public function getPlanoPrecoAtual($data)
    {
        return $this->planoPrecos()
            ->where('data_inicio', '<=', $data)
            ->where('data_fim', '>=', $data)
            ->orWhere('is_default', true)
            ->orderBy('is_default', 'desc')
            ->first();
    }
}