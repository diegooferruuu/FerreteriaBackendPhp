<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "cajas";
    protected $primaryKey = 'id_caja';
    protected $fillable = [
        'caja',
        'estado',
        'sucursal_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id')->withTrashed();
    }

    public function pos()
    {
        return $this->hasOne(PuntoVenta::class, 'caja_id', 'id_caja');
    }
}
