<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreHomologacionProductoRequest extends FormRequest
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

        if( $this->routeIs('homologacion_productos.import') ) {
            return [
                'archivo' => ['required', File::types(['csv', 'xlsx', 'xls','text'])],
            ];
        }

        if( $this->routeIs('homologacion_productos.store') ) {
            return [
                'producto_id' => 'required|exists:productos,id|unique:homologacion_productos',
                'estado' => 'in:ACTIVO,INACTIVO',
                'catalogo_producto_id' => [
                    'required',
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
                    'required',
                    Rule::exists('valores_catalogo', 'codigo_clasificador')->where(function ($query) {
                        return $query->where('id', $this->catalogo_producto_id);
                    })
                ],
            ];
        }
    }
}
