<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Impressora extends Model
{
    use HasFactory;

    protected $table = 'impressoras';

    protected $fillable = [
        'nome',
        'ip',
        'porta',
        'ativo',
        'tipo',
        'descricao',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'porta' => 'integer',
        'ordem' => 'integer',
    ];

    /**
     * Scope para impressoras ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para impressoras ordenadas
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}

