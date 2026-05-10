<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventoSignificativo extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = 'eventos_significativos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cufd_evento',
        'cafc',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'codigo_recepcion',
        'evento_id',
        'sucursal_id',
        'punto_venta_id',
    ];

    // Accessors & Mutators
    /**
     * Interact with the user's address.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function codigoEventoSiat(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->evento->codigo_clasificador,
        );
    }

    protected function descripcionEventoSiat(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->evento->descripcion,
        );
    }
    // end

    // QueryFilter
    protected $filters = ['sucursal_id', 'pos_id', 'estado'];

    public function pos_id($query, $value) {
        if( is_null($value) ) return $query;
        return $query->where('punto_venta_id', $value);
    }
    // end

    public function evento() {
        return $this->belongsTo(ValorCatalogo::class, 'evento_id', 'id');
    }

    public function sucursal() {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id')->withTrashed();
    }

    public function pos() {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id', 'id')->withTrashed();
    }

    public function facturas()
    {
        return $this->belongsToMany(Factura::class, 'evento_factura', 'evento_significativo_id', 'factura_id')->withTimestamps()->orderBy('id');
    }

    public function recepciones()
    {
        return $this->hasMany(Recepcion::class, 'evento_significativo_id', 'id');
    }


}
