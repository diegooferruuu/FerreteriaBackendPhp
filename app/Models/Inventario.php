<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use App\Http\Traits\QueryReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes, QueryFilter, QueryReport;
    protected $table = "inventario";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cantidad',
        'cantidad_maxima',
        'cantidad_minima',
        'producto_id',
        'sucursal_id',
        'precio_particular_id',
    ];

    // QueryFilter
    protected $filters = ['sucursal_id', 'producto_id', 'gt', 'gte', 'lt', 'lte', 'stock'];

    public function stock($query, $value) {
        return $query->when($value == 'agotado', function ($query) {
            return $query->whereColumn('cantidad', '<=', 'cantidad_minima');
        });
    }
    // end

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id')->withTrashed();
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id')->withTrashed();
    }



    public function inventarioMovimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'inventario_id','id');
    }

    public function ingresos()
    {
        return $this->inventarioMovimientos()->whereNotNull('ingresos');
    }
}
