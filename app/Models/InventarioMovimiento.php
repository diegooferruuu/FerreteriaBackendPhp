<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use App\Http\Traits\QueryReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioMovimiento extends Model
{
    use HasFactory, QueryFilter, QueryReport;
    protected $table = "inventario_movimiento";
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'inicial',
        'ingresos',
        'egresos',
        'precio',
        'identificador',
        'origen',
        'secuencial_origen',
        'fecha',
        'movimiento_id',
        'inventario_id'
    ];

    // QueryFilter
    protected $filters = ['movimiento_id', 'sucursal_id', 'producto_id'];

    public function sucursal_id($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereRelation('inventario', 'sucursal_id', $value);
    }

    public function producto_id($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereRelation('inventario', 'producto_id', $value);
    }
    // end

    public function originable() {
        return $this->morphTo(__FUNCTION__, 'origen', 'identificador');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_id', 'id')->withTrashed();
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id', 'id')->withTrashed();
    }

}
