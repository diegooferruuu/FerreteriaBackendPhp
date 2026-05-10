<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutorizacionSistema extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = 'autorizacion_sistema';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nit',
        'razon_social',
        'nombre_comercial',
        'version',
        'tipo',
        'codigo_sistema',
        'codigo_ambiente',
        'codigo_modalidad',
        'logo',
        'estado',
    ];
}
