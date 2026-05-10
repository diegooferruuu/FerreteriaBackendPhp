<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaBackRequest extends FormRequest
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
            'fecha' => 'required|date',
            'total' => 'required|numeric',
            'precio_neto' => 'required|numeric',
            'descuento' => 'nullable',
            'estado' => 'sometimes|required|in:ACTIVO,INACTIVO,ANULADO',
            'descripcion' => 'nullable',
            'informacion_tarjeta' => 'nullable|max:150',
            'metodo_pago_id' => 'required|integer|exists:metodos_pago,id',
            'sucursal_id' => 'required|integer|exists:sucursales,id',
            'punto_venta_id' => 'required|integer|exists:puntos_venta,id',
            'cliente_id' => 'required|integer|exists:clientes,id',
        ];
    }
}
