<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cufd extends Model
{
    use HasFactory, QueryFilter;

    protected $table = 'cufd';
    protected $primaryKey = 'id';
    protected $fillable = [
        'valor',
        'codigo_control',
        'validez',
        'estado',
        'cuis_id',
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

    public function cuis() {
        return $this->belongsTo(Cuis::class, 'cuis_id', 'id');
    }
}
