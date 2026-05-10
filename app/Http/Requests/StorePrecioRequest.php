<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrecioRequest extends FormRequest
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
            'precio_venta' => 'required|numeric|max:100',
            'descuento' => 'required|numeric|max:100',
            'autorizador_id' => 'required|integer',
            'registro_id' => 'required|integer',
        ];
    }
}
