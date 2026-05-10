<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departamento extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = "departamentos";
    protected $primaryKey = 'id';
    protected $fillable = [
        'departamento',
        'regional_id'
    ];

    public function regional()
    {
        return $this->belongsTo(Regional::class, 'regional_id', 'id_regional')->withTrashed();
    }

    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'departamento_id', 'id');
    }
}
