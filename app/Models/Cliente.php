<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo', 'estrangeiro', 'sexo', 'nome', 'data_nascimento', 'cpf', 
        'rg', 'passaporte', 'orgao_expedidor', 'estado_civil', 'inscricao_estadual_pf', 
        'cep', 'cidade', 'endereco', 'numero', 'complemento', 'bairro', 'estado',
        'pais', 'nacionalidade', 'email', 'email_alternativo', 'telefone', 'celular', 'profissao'
    ];

    // Relação com Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}
