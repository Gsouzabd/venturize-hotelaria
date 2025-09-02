<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $fillable = [
        'nome',
        'email',
        'senha', // Campo correto na tabela é 'senha'
        'tipo',
        'grupo_usuario_id',
        'fl_ativo',
    ];

    // Ensure the password is hashed when set
    public function setSenhaAttribute($senha)
    {
        $this->attributes['senha'] = bcrypt($senha);
    }

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