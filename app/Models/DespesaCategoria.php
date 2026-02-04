<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespesaCategoria extends Model
{
    use HasFactory;

    protected $table = 'despesa_categorias';

    protected $fillable = [
        'despesa_id',
        'categoria_despesa_id',
        'valor',
        'observacoes',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
    ];

    public function despesa()
    {
        return $this->belongsTo(Despesa::class);
    }

    public function categoriaDespesa()
    {
        return $this->belongsTo(CategoriaDespesa::class, 'categoria_despesa_id');
    }
}

