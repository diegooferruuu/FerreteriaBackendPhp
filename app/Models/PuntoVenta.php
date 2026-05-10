<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PuntoVenta extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = 'puntos_venta';
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_siat',
        'nombre',
        'descripcion',
        'estado',
        'tipo_punto_venta_id',
        'sucursal_id',
    ];
    protected $with = ['eventoSignificativo'];

    // Accessors & Mutators
    /**
     * Interact with the user's address.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function inEvent(): Attribute
    {

        return Attribute::make(
            get: fn () => $this->eventoSignificativo ? true : false,
        );
    }

    protected function isOffline(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->eventoSignificativo && in_array($this->eventoSignificativo->evento->codigo_clasificador, [1, 2, 3, 4, 5, 6, 7]) ? true : false,
        );
    }
    // end

    // QueryFilter
    protected $filters = ['sucursal_id', 'search'];

    public function search($query, $value) {
        return $query->whereLike(['nombre'], $value);
    }
    // end

    public function tipo()
    {
        return $this->belongsTo(ValorCatalogo::class, 'tipo_punto_venta_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id')->withTrashed();
    }

    public function sincronizacionCatalogo()
    {
        return $this->morphOne(SincronizacionCatalogo::class, 'syncable');
    }

    public function cuis()
    {
        return $this->hasOne(Cuis::class, 'punto_venta_id', 'id')->ofMany('id');
    }

    public function eventoSignificativo() {
        return $this->hasOne(EventoSignificativo::class, 'punto_venta_id', 'id')->ofMany([
            'id' => 'max'
        ], function ($query) {
            $query->whereIn('estado', ['INICIADO','FINALIZADO']);
//            $query->orWhere('cafc','!=',null);
        });
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'punto_venta_id', 'id');
    }

    public function facturas()
    {
        return $this->hasManyThrough(Factura::class, Venta::class, 'punto_venta_id', 'venta_id', 'id', 'id');
    }

    public function facturasPendientesRecepcion()
    {
        return $this->facturas()->where('facturas.estado', 'PENDIENTE');
    }
}
