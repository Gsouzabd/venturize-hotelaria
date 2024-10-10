<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = ['reserva_id', 'checkin_at'];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }
}