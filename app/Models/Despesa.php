<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Despesa extends Model
{
    use HasFactory;

    protected $table = 'despesas';

    protected $fillable = [
        'numero_nota_fiscal',
        'descricao',
        'arquivo_nota',
        'data',
        'valor_total',
        'observacoes',
        'usuario_id',
        'fornecedor_id',
    ];

    protected $casts = [
        'data' => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function despesaCategorias()
    {
        return $this->hasMany(DespesaCategoria::class);
    }

    public function categorias()
    {
        return $this->belongsToMany(CategoriaDespesa::class, 'despesa_categorias', 'despesa_id', 'categoria_despesa_id')
            ->withPivot('valor', 'observacoes')
            ->withTimestamps();
    }

    public function getValorTotalRateadoAttribute()
    {
        return $this->despesaCategorias->sum('valor');
    }

    public function getValorPendenteRateioAttribute()
    {
        return $this->valor_total - $this->valor_total_rateado;
    }

    public function isRateioCompleto()
    {
        $tolerancia = 0.01;
        $diferenca = abs($this->valor_total - $this->valor_total_rateado);
        return $diferenca <= $tolerancia;
    }

    public function scopePorPeriodo($query, $dataInicial, $dataFinal)
    {
        return $query->whereBetween('data', [$dataInicial, $dataFinal]);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->whereHas('despesaCategorias', function ($q) use ($categoriaId) {
            $q->where('categoria_despesa_id', $categoriaId);
        });
    }

    public function scopePorFornecedor($query, $fornecedorId)
    {
        return $query->where('fornecedor_id', $fornecedorId);
    }
}

