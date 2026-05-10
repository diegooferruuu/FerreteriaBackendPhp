<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cafc extends Model
{
    use HasFactory;
    protected $table = "cafc";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cafc',
        'numero_inicio',
        'numero_fin',
        'numero_facturas_utilizadas',
        'estado',
        'sucursal_id',
    ];
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }
}
