<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateEmisionMasivaRequest extends FormRequest
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
        if ($this->route()->getName() === 'emisiones-masivas.update' ) {
            return [
                'descripcion' => 'required|string|max:150',
                'fecha_envio' => 'nullable|date',
                'sucursal_id' => 'required_without:punto_venta_id|prohibits:punto_venta_id|integer|exists:sucursales,id',
                'punto_venta_id' => 'required_without:sucursal_id|prohibits:sucursal_id|integer|exists:puntos_venta,id',
//            'estado' => 'in:INICIADO,FINALIZADO',
            ];
        }
        return [
            'descripcion' => 'required|string|max:150',
            'fecha_envio' => 'nullable|date',
            'sucursal_id' => 'required_without:punto_venta_id|prohibits:punto_venta_id|integer|exists:sucursales,id',
            'punto_venta_id' => 'required_without:sucursal_id|prohibits:sucursal_id|integer|exists:puntos_venta,id',
//            'estado' => 'in:INICIADO,FINALIZADO',
        ];
    }
}
