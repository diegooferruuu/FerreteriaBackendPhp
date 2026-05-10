<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrecioGeneralRequest extends FormRequest
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
            'precio_menor' => 'required|numeric',
            'descuento_menor' => 'required|numeric',
            'precio_mayor' => 'required|numeric',
            'descuento_mayor' => 'required|numeric',
            'estado' => 'required|in:ACTIVO,INACTIVO',//definir estados
            'producto_id' => 'required|exists:productos,id',
            'carga_precio_id' => 'required|exists:carga_precios,id',
        ];
    }
}
