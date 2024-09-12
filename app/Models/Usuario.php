<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'tipo',
        'grupo_usuario_id',
        'fl_ativo',
    ];

    // Relação com Grupo de Usuário
    public function grupoUsuario()
    {
        return $this->belongsTo(GrupoUsuario::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'cliente_id');
   
    }

    // Verificar permissões
    public function temPermissao(string $permissao): bool
    {
        return $this->grupoUsuario->permissoes->contains('nome', $permissao);
    }
}
