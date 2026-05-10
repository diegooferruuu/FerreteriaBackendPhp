<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrecioGeneral extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = 'precios_general';
    protected $primaryKey = 'id';
    protected $fillable = [
        'precio_menor',
        'descuento_menor',
        'precio_mayor',
        'descuento_mayor',
        'estado',
        'producto_id',
        'carga_precio_id',
    ];

    // QueryFilter
    protected $filters = ['producto_id'];
    // end

    public function cargaPrecio()
    {
        return $this->belongsTo(CargaPrecio::class, 'carga_precio_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'precio_general_id', 'id')->withTrashed();
    }
}
