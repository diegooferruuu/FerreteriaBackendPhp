<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrecioGeneralRequest extends FormRequest
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
            'precio_menor' => 'sometimes|required|numeric',
            'descuento_menor' => 'sometimes|required|numeric',
            'precio_mayor' => 'sometimes|required|numeric',
            'descuento_mayor' => 'sometimes|required|numeric',
            'estado' => 'sometimes|required|in:ACTIVO,INACTIVO',
            'producto_id' => 'required|exists:productos,id',
            'carga_precio_id' => 'required|exists:carga_precios,id',
        ];
    }
}
