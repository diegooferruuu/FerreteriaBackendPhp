<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Firma extends Model
{
    use HasFactory;
    protected $table = 'firmas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'certificado',
        'llave_privada',
        'validez',
        'estado',
    ];
}
