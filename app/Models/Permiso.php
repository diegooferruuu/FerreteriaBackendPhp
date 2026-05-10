<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Permiso extends Model
{
    use HasFactory, QueryFilter, HasSlug;

    protected $table = "permisos";
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $fillable = ['permiso', 'slug'];
    protected $hidden = ['created_at', 'updated_at'];


    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('permiso')
            ->saveSlugsTo('slug');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'permiso_rol', 'permiso_id', 'rol_id');
    }
}
