<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogoFacturacion extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'catalogos_facturacion';
    protected $primaryKey = 'id';
    protected $guarded = ['*'];
}
