<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atributo extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "atributos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'atributo'
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_atributo', 'atributo_id', 'producto_id')
        ->withPivot('valor')
        ->withTimestamps()
        ->wherePivotNull('producto_atributo.deleted_at');
    }
}
