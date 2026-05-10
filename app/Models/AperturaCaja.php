<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AperturaCaja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "aperturas_caja";
    protected $primaryKey = 'id_apertura_caja';
    protected $fillable = [
        'monto_apertura',
        'fecha_apertura',
        'observaciones',
        'estado',
        'usuario_id',
        'caja_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id')->withTrashed();
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id', 'id_caja')->withTrashed();
    }
}
