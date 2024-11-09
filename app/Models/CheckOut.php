<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    use HasFactory;

    protected $fillable = ['reserva_id', 'checkout_at'];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}