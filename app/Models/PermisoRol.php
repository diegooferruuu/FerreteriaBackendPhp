<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermisoRol extends Model
{
    use HasFactory;
    protected $table = "permiso_rol";
    public $timestamps = false;
    protected $fillable = ['permiso_id', 'rol_id'];
}
