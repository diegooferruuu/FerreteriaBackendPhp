<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArrayDetalleVentaRequest extends FormRequest
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
            'items' => 'required|filled|array',
            'items.*.cantidad' => 'required|numeric',
            'items.*.descuento' => 'required|numeric',
            'items.*.precio' => 'required|numeric',
            'items.*.sub_total' => 'required|numeric',
            'items.*.inventario_id' => 'required|integer|exists:inventario,id',
        ];
    }
}
