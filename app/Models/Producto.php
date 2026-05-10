<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Producto extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'productos'; //por defecto model_name in plural => productos
    protected $primaryKey = 'id'; // por defecto id
    public $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id',
        'producto',
        'descripcion',
        'imagen',
        'codigo_barra',
        'codigo_qr',
        'estado',
        'procedencia_id',
        'unidad_medida_id',
        'clasificacion_producto_id',
    ];

    public function id(): Attribute
    {
        return new Attribute(
            set: function ($id, $attributes){
                return trim($id);
            },
        );
    }

    public function clasificacionProducto()
    {
        return $this->belongsTo(ClasificacionProducto::class, 'clasificacion_producto_id', 'id')->withTrashed();
    }

    public function procedencia()
    {
        return $this->belongsTo(Procedencia::class, 'procedencia_id', 'id')->withTrashed();
    }
    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id', 'id')->withTrashed();
    }
    public function precioGeneral()
    {
        return $this->hasOne(PrecioGeneral::class, 'producto_id', 'id');
    }


    public  function homologacion()
    {
        return $this->hasOne(HomologacionProducto::class, 'producto_id', 'id');
    }


    //relacion uno a uno detallecompra a producto inverso
    //    public function detalleCompra()
    //    {
    //        return $this->belongsTo(DetalleCompra::class,'producto_id','id');
    //    }
    //relacion 1 a n
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'producto_id', 'id');
    }
    //relacion mucho s a muchos
    public function atributos()
    {
        return $this->belongsToMany(Atributo::class, 'producto_atributo', 'producto_id', 'atributo_id')
            ->withPivot('valor')
            ->withTimestamps()
            ->wherePivotNull('producto_atributo.deleted_at')
            ->withTrashed();
    }
}
