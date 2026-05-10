<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnulacionFactura extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'anulacion_facturas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'factura_id',
        'motivo_id',
        'descripcion',
        'codigo_estado',
        'codigo_descripcion',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class, 'factura_id', 'id');
    }

    public function motivo()
    {
        return $this->belongsTo(ValorCatalogo::class, 'motivo_id', 'id');
    }
}
