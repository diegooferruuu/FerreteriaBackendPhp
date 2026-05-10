<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMovimientoPresupuestoRequest extends FormRequest
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
            'presupuesto_origen' => 'required|integer',
            'presupuesto_destino' => 'required|integer',
            'monto' => 'required|integer',
            'autorizador_id' => 'nullable|string|max:20',
            'fecha_autorizacion' => 'nullable|date',
            'observaciones' => 'nullable|string|max:255',
        ];
    }
}
