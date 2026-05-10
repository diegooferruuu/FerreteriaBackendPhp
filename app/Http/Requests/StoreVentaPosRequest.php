<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaPosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'codigo_secuencia' => 'nullable',
            'total' =>'required',
            'descuento' =>'required',
            'fecha' =>'required|date',
            'descripcion' => 'nullable',
            'metodo_pago_id' =>'required|exists:metodos_pago,id_id',
            'sucursal_id' =>'required|exists:sucursales,id_id',
            'cliente_id' => 'required|exists:clientes,id_id',
            'tipo_venta_id' => 'required|exists:tipo_venta,tipo_venta_id',
            'caja_id' => 'nullable|exists:cajas,id_caja_id',
        ];
    }
}
