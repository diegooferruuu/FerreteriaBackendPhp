<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proforma extends Model
{
    use HasFactory;
    protected $table = "proformas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_secuencia',
        'total',
        'descuento',
        'descuentoMonto',
        'fecha',
        'vigencia',
        'descripcion',
        'estado',
        'sucursal_id',
        'cliente_id',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class,'detalle_proforma','proforma_id','producto_id')
            ->withPivot(['cantidad','precio','descuento','descuentoMonto','sub_total','codigo_producto_mayor_menor'])
            ->withTimestamps();
//            ->wherePivotNull('detalle_proforma.deleted_at');
    }


}

