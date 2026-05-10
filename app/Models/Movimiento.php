<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimiento extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "movimientos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'movimiento',
        'abreviatura',
        'tipo',
        'estado'
    ];
}
