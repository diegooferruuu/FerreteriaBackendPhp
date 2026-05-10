<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePrecioMasivoRequest extends FormRequest
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
//        dd($this->request);
        $rules = [
            'descripcion' => 'required|string|in:mayor,menor,ambos',
            'departamentos' => 'required_if:descripcion,departamento|array|filled',
            'departamentos.*' => 'required_if:descripcion,departamento|distinct|integer|exists:departamentos,id',
            'sucursales' => 'required_if:descripcion,sucursal|array|filled',
            'sucursales.*' => 'required_if:descripcion,sucursal|distinct|integer|exists:localidades,id_localidad',
            'subido_por' => 'required|integer|exists:usuarios,id',
        ];

        if( $this->route()->named('carga_precios.import') ) {
            $rules = array_merge($rules, [
                'archivo' => ['required', File::types(['csv', 'xlsx', 'xls'])],
            ]);
        }

        if( $this->route()->named('carga_precios.store_many') ) {
            $rules = array_merge($rules, [
                'precios' => 'required|array|filled',
                'precios.*.producto_id' => 'required|distinct|string|exists:productos,id',
                'precios.*.precio_menor' => 'bail|numeric|gte:0',
                'precios.*.descuento_menor' => 'bail|numeric|gte:0',
                'precios.*.precio_mayor' => 'bail|numeric|gte:0',
                'precios.*.descuento_mayor' => 'bail|numeric|gte:0',
            ]);
        }

        return $rules;
    }
}
