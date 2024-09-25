<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $fillable = [
        'razao_social',       // Razão Social
        'nome_fantasia',      // Nome Fantasia
        'cnpj',               // CNPJ
        'inscricao_estadual', // Inscrição Estadual (IE)
        'email',              // Email
        'telefone',           // Telefone
        'cep',                // CEP
    ];
}