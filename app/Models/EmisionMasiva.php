<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmisionMasiva extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'emisiones_masivas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cufd_evento',
        'descripcion',
        'fecha_envio',
        'estado',
        'codigo_recepcion',
        'sucursal_id',
        'punto_venta_id',
    ];


    public function sucursal() {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id')->withTrashed();
    }

    public function pos() {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id', 'id')->withTrashed();
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'facturas_masivas', 'emision_masiva_id', 'factura_id')->withTimestamps();
    }

    public function recepciones()
    {
        return $this->hasMany(Recepcion::class, 'emision_masiva_id', 'id');
    }
}
