<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'tipo', 'estrangeiro', 'sexo', 'nome', 'data_nascimento', 'cpf', 
        'rg', 'passaporte', 'orgao_expedidor', 'estado_civil', 'inscricao_estadual_pf', 
        'cep', 'cidade', 'endereco', 'numero', 'complemento', 'bairro', 
        'pais', 'email', 'email_alternativo', 'telefone', 'celular', 'profissao'
    ];

    // Relação com Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}
