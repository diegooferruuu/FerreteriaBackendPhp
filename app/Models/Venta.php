<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use App\Http\Traits\QueryReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory, QueryFilter, QueryReport;
    protected $table = "ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'codigo_secuencia',
        'total',
        'descuento',
        'fecha',
        'descripcion',
        'informacion_tarjeta',
        'estado',
        'metodo_pago_id',
        'sucursal_id',
        'cliente_id',
        'punto_venta_id',
    ];

    // QueryFilter
    protected $filters = ['cliente_id', 'punto_venta_id', 'producto_id', 'between'];

    public function producto_id($query, $value) {
        if( is_null($value) ) return $query;
        return $query->whereRelation('inventarios', 'producto_id', $value);
    }
    // end

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id', 'id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function pos() {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    //relacion n a na ventas a inventario

    public function inventarios()
    {
        return $this->belongsToMany(Inventario::class,'detalle_venta','venta_id','inventario_id')
            ->withPivot(['cantidad','precio','descuento','sub_total','deleted_at'])
            ->withTimestamps()
            ->wherePivotNull('detalle_venta.deleted_at');
    }

    public function factura()
    {
        return $this->hasOne(Factura::class, 'venta_id', 'id');
    }
}
