<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use App\Http\Traits\QueryReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes, QueryFilter, QueryReport;

    protected $table = 'facturas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'venta_id',
        'cufd_id',
        'xml',
        'codigo_documento_sector',
        'codigo_tipo_factura',
        'numero_documento_identidad',
        'codigo_documento_identidad',
        'codigo_metodo_pago',
        'codigo_cliente',
        'razon_social',
        'leyenda',
        'usuario',
        'cuf',
        'cafc',
        'estado',
    ];

    // QueryFilter
    protected $filters = ['pos_id', 'estado'];

    public function pos_id($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereRelation('venta', 'punto_venta_id', $value);
    }
    // end

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'id');
    }

    public function cufd()
    {
        return $this->belongsTo(Cufd::class, 'cufd_id', 'id');
    }

    public function recepciones()
    {
        return $this->belongsToMany(Recepcion::class, 'factura_recepcion', 'factura_id', 'recepcion_id')
        ->withPivot('codigo_estado', 'nro_archivo')
        ->withTimestamps();
    }

    public function eventosSignificativos()
    {
        return $this->belongsToMany(EventoSignificativo::class, 'evento_factura', 'factura_id', 'evento_significativo_id')->withTimestamps();
    }
    public function emisionesMasivas()
    {
        return $this->belongsToMany(EmisionMasiva::class, 'facturas_masivas', 'factura_id', 'emision_masiva_id')->withTimestamps();
    }
}
