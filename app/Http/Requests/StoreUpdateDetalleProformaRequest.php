<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateDetalleProformaRequest extends FormRequest
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
            'detalleProforma.*.cantidad' =>'required|numeric|gt:0',
            'detalleProforma.*.precio' =>'required|numeric|gt:0',
            'detalleProforma.*.descuento' =>'required',
            'detalleProforma.*.descuentoMonto' =>'nullable',
            'detalleProforma.*.sub_total' =>'required|numeric|gt:0',
            'detalleProforma.*.producto_id' =>'required|exists:productos,id',
            'detalleProforma.*.codigo_producto_mayor_menor' =>'required',
        ];
    }
}
