<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Perfil extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;
    protected $table = 'perfiles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nombres',
        'apellidos',
        'telefono',
        'celular',
        'foto',
        'usuario_id',
    ];

    /* Relación de tablas 1:1 usuario con persona */
    public function usuario()
    {
        /* 1 usuario pertenece a una persona */
        return $this->belongsTo(Usuario::class, 'usuario_id')->select('id', 'usuario');
    }

    /**
     * Genera un identificador unico y almacena la imagen
     */
    public static function setImagen($foto, $edit = false)
    {
        if ($foto) {
            if ($edit) {
                unlink("./$edit");
            }
            $extension = $foto->extension();
            $path = './perfil/';
            $image = Str::random(25) . '.' . $extension;
            $foto->move($path, $image);
            $full_path = "/perfil/$image";

            return $full_path;
        } else {
            return false;
        }
    }
}
