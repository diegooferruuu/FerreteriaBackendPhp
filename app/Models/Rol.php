<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'roles'; // nombre de la tabla
    public $primaryKey = 'id'; // campo de la llave primaria
    protected $guarded = ['id'];
    protected $fillable = ['rol', 'slug', 'descripcion']; // atributos de la tabla
    protected $hidden = ['created_at', 'updated_at'];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }
}
