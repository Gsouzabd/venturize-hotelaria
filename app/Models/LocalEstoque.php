<?php

namespace App\Models;

use App\Models\Estoque;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocalEstoque extends Model
{
    use HasFactory;

    protected $table = 'locais_estoque';

    protected $fillable = ['nome'];

    public function estoques()
    {
        return $this->hasMany(Estoque::class, 'local_estoque_id');
    }
}
