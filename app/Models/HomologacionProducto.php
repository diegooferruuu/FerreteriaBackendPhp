<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomologacionProducto extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'homologacion_productos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'producto_id',
        'catalogo_producto_id',
        'codigo_siat',
        'estado'
    ];
    public function producto_id(): Attribute
    {
        return new Attribute(
            set: function ($id, $attributes){
                return trim($id);
            },
        );
    }
    public function producto() {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    public function catalogoProducto() {
        return $this->belongsTo(ValorCatalogo::class, 'catalogo_producto_id', 'id');
    }
}
