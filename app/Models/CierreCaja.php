<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CierreCaja extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "cierres_caja";
    protected $primaryKey = 'id_cierre_caja';
    protected $fillable = [
        'monto_esperado',
        'fecha_cierre',
        'observaciones',
        'estado',
        'usuario_id',
        'caja_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id')->withTrashed();
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id', 'id_caja')->withTrashed();
    }

    public function cierresGenerales()
    {
        return $this->belongsToMany(CierreGeneral::class, 'cierre_caja_informacion', 'cierre_caja_id', 'cierre_general_id')
        ->withTimestamps()
        ->wherePivotNull('cierre_caja_informacion.deleted_at');
    }
}
