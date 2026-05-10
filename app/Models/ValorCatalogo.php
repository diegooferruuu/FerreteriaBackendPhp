<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValorCatalogo extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'valores_catalogo';
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_clasificador',
        'codigo_actividad',
        'descripcion',
        'estado',
        'sincronizacion_catalogo_id',
    ];

    // QueryFilter
    protected $filters = ['catalogo_facturacion_id', 'sucursal_id', 'pos_id', 'search'];

    public function catalogo_facturacion_id ($query, $value) {
        if( is_null($value) ) return $query;
        return $query->catalogo($value);
    }

    public function sucursal_id ($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereHas('sincronizacion', function (Builder $query) use ($value) {
            $query->where('syncable_type', 'sucursal')->where('syncable_id', $value);
        });
    }

    public function pos_id ($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereHas('sincronizacion', function (Builder $query) use ($value) {
            $query->where('syncable_type', 'pos')->where('syncable_id', $value);
        });
    }

    public function search($query, $value) {
        return $query->whereLike(['descripcion'], $value);
    }
    // end

    // Local Scope
    public function scopeCatalogo($query, $catalogoId) {
        return $query->whereRelation('sincronizacion', 'catalogo_facturacion_id', $catalogoId);
    }
    // end

    public function sincronizacion() {
        return $this->belongsTo(SincronizacionCatalogo::class, 'sincronizacion_catalogo_id', 'id');
    }
}
