<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDespesa extends Model
{
    use HasFactory;

    protected $table = 'categorias_despesas';

    protected $fillable = [
        'nome',
        'descricao',
        'fl_ativo',
    ];

    protected $casts = [
        'fl_ativo' => 'boolean',
    ];

    public function despesaCategorias()
    {
        return $this->hasMany(DespesaCategoria::class, 'categoria_despesa_id');
    }

    public function despesas()
    {
        return $this->hasManyThrough(Despesa::class, DespesaCategoria::class, 'categoria_despesa_id', 'id', 'id', 'despesa_id');
    }

    public function scopeAtivas($query)
    {
        return $query->where('fl_ativo', true);
    }
}

