<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Recepcion extends Model
{
    use HasFactory;

    protected $table = 'recepciones';
    protected $primaryKey = 'id';
    protected $fillable = [
        'evento_significativo_id',
        'emision_masiva_id',
        'sucursal_id',
        'punto_venta_id',
        'tipo',
        'codigo_emision',
        'fecha_envio',
        'archivo',
        'hash_archivo',
        'cantidad_facturas',
        'codigo_recepcion',
        'mensaje_observacion',
        'codigo_estado',
        'codigo_descripcion',
        'estado',
        'codigo_documento_sector',
        'codigo_documento_fiscal',
    ];

//    public function mensaje_observacion(): Attribute
//    {
//        return new Attribute(
//
//            get: fn ($mensaje_observacion) =>  dd($mensaje_observacion), // json_decode($mensaje_observacion),
//            set: fn ($mensaje_observacion) =>  dd($mensaje_observacion),//json_encode($mensaje_observacion)
//        );
//    }

    public function eventoSignificativo() {
        return $this->belongsTo(EventoSignificativo::class, 'evento_significativo_id', 'id');
    }
    public function emisionMasiva() {
        return $this->belongsTo(EmisionMasiva::class);
    }
    public function sucursal() {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function pos() {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id', 'id');
    }

    public function facturas() {
        return $this->belongsToMany(Factura::class, 'factura_recepcion', 'recepcion_id', 'factura_id')
        ->withPivot('codigo_estado', 'nro_archivo')
        ->withTimestamps();
    }
}
