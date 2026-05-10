<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClasificacionProducto extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "clasificaciones_producto";
    protected $primaryKey = 'id';
    protected $fillable = [
        'clasificacion'
    ];
}
