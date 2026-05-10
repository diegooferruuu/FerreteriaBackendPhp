<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateProformaRequest extends FormRequest
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
        if ($this->route()->getName() === 'proformas.update' ) {
            return [
                'total' => 'required|numeric|gt:0',
//                'totalAntesDescuento' => 'required|numeric|gt:0',
                'descuento' => 'required',
                'descuentoMonto' => 'nullable',
                'fecha' => 'nullable|date',
                'vigencia' => 'required|date',
                'descripcion' => 'nullable',
                'sucursal_id' => 'required|exists:sucursales,id',
                'cliente_id' => 'required',
            ];
        }
        return [
            'total' => 'required|numeric|gt:0',
//            'totalAntesDescuento' => 'required|numeric|gt:0',
            'descuento' => 'required',
            'descuentoMonto' => 'nullable',
            'fecha' => 'nullable|date',
            'vigencia' => 'required|date',
            'descripcion' => 'nullable',
            'sucursal_id' => 'required|exists:sucursales,id',
            'cliente_id' => 'required',
        ];
    }
}
