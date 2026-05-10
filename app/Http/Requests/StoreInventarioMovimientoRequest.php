<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventarioMovimientoRequest extends FormRequest
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
            'inicial' => 'nullable|numeric',
            'ingresos' => 'nullable|numeric',
            'egresos' => 'nullable|numeric',
            'precio' => 'nullable|numeric',
            'identificador' => 'required|string|max:150',
            'origen' => 'required|string|max:150',
            'secuencial_origen'=>'nullable',
            'fecha' => 'required|date',
            'fecha_vencimiento' => 'nullable|date',
            'movimiento_id' => 'required|integer|exists:movimientos,id',
            'inventario_id' => 'required|integer|exists:inventario,id',
        ];
    }
}
