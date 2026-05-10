<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoImpresion extends Model
{
    use HasFactory;
    protected $table = "tipo_impresion";
    protected $primaryKey = 'id';
    protected $fillable = [
        'tipo',
        'tipo_siat'
    ];
    //relacion a impresion
    public function usuario()
    {
        return $this->belongsToMany(Usuario::class,'usuario_tipo_impresion')->withTimestamps();
    }



}
