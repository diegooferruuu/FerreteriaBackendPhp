<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreGeneral extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "cierres_general";
    protected $primaryKey = 'id';
    protected $fillable = [
        'monto_esperado',
        'monto_total',
        'fecha_cierre',
        'observaciones',
        'usuario_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id')->withTrashed();
    }

    public function cierresCajas()
    {
        return $this->belongsToMany(CierreCaja::class, 'cierre_caja_informacion', 'cierre_general_id', 'cierre_caja_id')
        ->withTimestamps()
        ->wherePivotNull('cierre_caja_informacion.deleted_at');
    }
}
