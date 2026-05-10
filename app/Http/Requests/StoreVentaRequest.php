<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
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
            'total' => 'required|numeric|gt:0',
            'fecha' => 'required|date',
            'descuento' => 'required',
            'descripcion' => 'nullable',
//            'informacion_tarjeta' => 'nullable|max:150'
            'metodo_pago_id' => 'required|exists:metodos_pago,id',
            'informacion_tarjeta' => 'sometimes|required_unless:metodo_pago_id,1|min:8|max:8',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cliente_id' => 'required',
            'punto_venta_id' => 'required|exists:puntos_venta,id',
        ];
    }
}
