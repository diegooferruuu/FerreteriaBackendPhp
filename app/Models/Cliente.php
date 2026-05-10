<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, QueryFilter;

    protected $table = "clientes";
    protected $primaryKey = 'id';
    protected $fillable = [
        'razon_social',
        'cedula_nit',
        'complemento',
        'telefono',
        'email',
        'direccion',
        'verificacion',
        'estado',
        'departamento_id',
        'tipo_documento_id'
    ];

    // QueryFilter
    protected $filters = ['search'];

    public function search($query, $value) {
        return $query->whereLike(['razon_social', 'cedula_nit', 'email'], $value);
    }
    // end

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(ValorCatalogo::class, 'tipo_documento_id', 'id');
    }
}
