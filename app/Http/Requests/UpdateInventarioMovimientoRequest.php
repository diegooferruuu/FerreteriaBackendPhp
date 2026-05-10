<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventarioMovimientoRequest extends FormRequest
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
            'identificador' => 'nullable|string|max:150',
            'origen' => 'nullable|string|max:150',
            'fecha' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'movimiento_id' => 'nullable|integer|exists:movimientos,id',
            'inventario_id' => 'nullable|integer|exists:inventarios,id',
        ];
    }
}
