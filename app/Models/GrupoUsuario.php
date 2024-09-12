<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoUsuario extends Model
{

    protected $table = 'grupo_usuarios';

    protected $fillable = ['nome'];

    // Relação com Permissões
    public function permissoes()
    {
        return $this->belongsToMany(Permissao::class, 'grupo_permissoes');
    }

    // Relação com Usuários
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}
