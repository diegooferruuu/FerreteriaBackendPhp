<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadMedida extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "unidad_medidas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'unidad_medida',
        'valor_catalogo_id'
    ];

    public function valorCatalogo() {
        return $this->belongsTo(ValorCatalogo::class, 'valor_catalogo_id', 'id');
    }
}
