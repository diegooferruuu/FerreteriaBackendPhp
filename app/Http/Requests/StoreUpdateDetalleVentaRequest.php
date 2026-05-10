<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateDetalleVentaRequest extends FormRequest
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
            'detalleVenta.*.cantidad' =>'required|numeric|gt:0',
            'detalleVenta.*.precio' =>'required|numeric|gt:0',
            'detalleVenta.*.descuento' =>'required',
            'detalleVenta.*.sub_total' =>'required|numeric|gt:0',
            'detalleVenta.*.inventario_id' =>'required|exists:inventario,id',
        ];
    }
}
