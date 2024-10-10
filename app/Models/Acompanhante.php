<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acompanhante extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'reserva_id',
        'nome',
        'cpf',
        'data_nascimento',
        'tipo',
        'email',         // Added email
        'telefone',      // Added telefone
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}