<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CargaPrecio extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = 'carga_precios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'descripcion',
        'estado',
        'subido_por',
        'autorizado_por',
    ];

    public function subidor()
    {
        return $this->belongsTo(Usuario::class, 'subido_por', 'id')->withTrashed();
    }

    public function autorizador()
    {
        return $this->belongsTo(Usuario::class, 'autorizado_por', 'id')->withTrashed();
    }
}
