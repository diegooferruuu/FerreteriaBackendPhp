<?php

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePuntoVentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nombre' => 'required|string|max:150|unique:puntos_venta',
            'codigo_siat' => 'unique:puntos_venta,codigo_siat',
            'descripcion' => 'required|string|max:200',
//            'estado' => 'required|in:ACTIVO,INACTIVO',
            'tipo_punto_venta_id' => [
                'required',
                'integer',
                Rule::exists('valores_catalogo', 'id')->where(function ($query) {
                    return $query->whereExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sincronizacion_catalogos')
                            ->whereColumn('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                            ->where('sincronizacion_catalogos.catalogo_facturacion_id', 13);
                    });
                }),
            ],
            'sucursal_id' => 'required|integer|exists:sucursales,id',

        ];
    }
}
