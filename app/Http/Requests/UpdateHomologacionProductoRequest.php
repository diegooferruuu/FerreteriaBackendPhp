<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateHomologacionProductoRequest extends FormRequest
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
            'producto_id' => [
                'sometimes',
                'required',
                'exists:productos,id',
                Rule::unique('homologacion_productos')->ignore($this->homologacionProducto, 'id')
            ],
            'estado' => 'in:ACTIVO,INACTIVO',
            'catalogo_producto_id' => [
                'required_with:codigo_siat',
                'integer',
                Rule::exists('valores_catalogo', 'id')->where(function ($query) {
                    return $query->whereExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sincronizacion_catalogos')
                            ->whereColumn('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                            ->where('sincronizacion_catalogos.catalogo_facturacion_id', 14);
                    });
                }),
            ],
            'codigo_siat' => [
                'required_with:catalogo_producto_id',
                Rule::exists('valores_catalogo', 'codigo_clasificador')->where(function ($query) {
                    return $query->where('id', $this->catalogo_producto_id);
                })
            ],
        ];
    }
}
