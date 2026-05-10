<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Precio extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "precios_particular";
    protected $primaryKey = 'id_precio_particular';
    protected $fillable = [
        'precio_venta',
        'descuento',
        'autorizador_id',
        'registro_id'
    ];
}
