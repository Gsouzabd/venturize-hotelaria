<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $fillable = ['nome'];
    protected $table = 'permissoes'; // Nome correto da tabela

    // Relação com Grupos de Usuários
    public function grupos()
    {
        return $this->belongsToMany(GrupoUsuario::class, 'grupo_permissoes');
    }
}
