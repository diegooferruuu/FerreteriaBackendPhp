<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuis extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'cuis';
    protected $primaryKey = 'id';
    protected $fillable = [
        'valor',
        'validez',
        'estado',
        'sucursal_id',
        'punto_venta_id',
    ];

    // Accessors & Mutators
    /**
     * Interact with the user's address.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function estadoValidez(): Attribute
    {
        return Attribute::make(
            get: fn () => Carbon::now()->lte(Carbon::parse($this->validez)) ? 'VIGENTE' : 'CADUCADO',
        );
    }
    // end

    public function sucursal() {
        return $this->belongsTo(Sucursal::class, 'sucursal_id', 'id');
    }

    public function pos() {
        return $this->belongsTo(PuntoVenta::class, 'punto_venta_id', 'id');
    }

    public function cufd() {
        return $this->hasOne(Cufd::class, 'cuis_id', 'id')->ofMany('id');
    }
}
