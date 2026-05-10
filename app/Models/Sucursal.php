<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sucursal extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "sucursales";
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_siat',
        'nombres',
        'abreviatura',
        'direccion',
        'latitud',
        'longitud',
        'departamento_id',
        'telefono',
        'email',
        'estado',
    ];
    // protected $with = ['eventoSignificativo'];

    // Accessors & Mutators
    /**
     * Interact with the user's address.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function inEvent(): Attribute
    {
        $this->load('eventoSignificativo');
        return Attribute::make(
            get: fn () => $this->eventoSignificativo ? true : false,
        );
    }

    protected function isOffline(): Attribute
    {
        $this->load('eventoSignificativo');
        return Attribute::make(
            get: fn () => $this->eventoSignificativo && in_array($this->eventoSignificativo->evento->codigo_clasificador, [1, 2, 3, 4,5,6,7]) ? true : false,
        );
    }
    // end

    // QueryFilter
    protected $filters = ['departamento_id', 'search'];

    public function search($query, $value) {
        return $query->whereLike(['nombres'], $value);
    }
    // end

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'id_almacen')->withTrashed();
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }



    public function sincronizacionCatalogo()
    {
        return $this->morphOne(SincronizacionCatalogo::class, 'syncable');
    }

    public function cuis()
    {
        return $this->hasOne(Cuis::class, 'sucursal_id', 'id')->ofMany('id');
    }

    public function eventoSignificativo() {
        return $this->hasOne(EventoSignificativo::class, 'sucursal_id', 'id')->ofMany([
            'id' => 'max'
        ], function ($query) {
            $query->whereIn('estado', ['INICIADO','FINALIZADO']);
        });
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'sucursal_id', 'id');
    }

    public function cufd()
    {
        return $this->hasOneThrough(Cufd::class, Cuis::class, 'sucursal_id', 'cuis_id', 'id', 'id');
    }

    public function puntosVenta()
    {
        return $this->hasMany(PuntoVenta::class, 'sucursal_id', 'id');
    }

    public function facturas()
    {
        return $this->hasManyThrough(Factura::class, Venta::class, 'sucursal_id', 'venta_id', 'id', 'id');
    }

    public function cuisPosCaducos()
    {
        return $this->puntosVenta()->whereHas('cuis', function(Builder $query) {
            $query->whereDate('validez', '<', now());
        });
    }

    public function cufdPosCaducos()
    {
        return $this->puntosVenta()->whereHas('cuis.cufd', function(Builder $query) {
            $query->whereDate('validez', '<', now());
        });
    }

    public function facturasPendientesRecepcion()
    {
        return $this->facturas()->where('facturas.estado', 'PENDIENTE');
    }
}
