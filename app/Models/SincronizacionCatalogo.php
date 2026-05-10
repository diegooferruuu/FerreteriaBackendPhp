<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SincronizacionCatalogo extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'sincronizacion_catalogos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'catalogo_facturacion_id',
        'syncable_id',
        'syncable_type',
    ];

    public function catalogo() {
        return $this->belongsTo(CatalogoFacturacion::class, 'catalogo_facturacion_id', 'id');
    }

    public function syncable() {
        return $this->morphTo(__FUNCTION__, 'syncable_type', 'syncable_id');
    }

    public function valores() {
        return $this->hasMany(ValorCatalogo::class, 'sincronizacion_catalogo_id', 'id');
    }
}
