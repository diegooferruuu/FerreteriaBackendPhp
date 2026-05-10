<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Almacen extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "almacenes";
    protected $primaryKey = 'id_almacen';
    protected $fillable = [
        'nombres',
        'abreviatura',
        'direccion',
        'latitud',
        'longitud',
        'telefono',
        'estado',
        'localidad_id'
    ];

    public function sucursal()
    {
        return $this->hasOne(Sucursal::class, 'almacen_id')->withTrashed();
    }

    public function localidad()
    {
        return $this->belongsTo(Localidad::class, 'localidad_id', 'id_localidad')->withTrashed();
    }
}
