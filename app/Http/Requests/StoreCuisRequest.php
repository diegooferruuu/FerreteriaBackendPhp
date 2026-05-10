<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCuisRequest extends FormRequest
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
            'sucursal_id' => 'required_without:punto_venta_id|prohibits:punto_venta_id|integer|exists:sucursales,id',
            'punto_venta_id' => 'required_without:sucursal_id|prohibits:sucursal_id|integer|exists:puntos_venta,id',
        ];
    }

    /**
    * Prepare the data for validation.
    *
    * @return void
    */
    protected function prepareForValidation()
    {
        $this->merge([
            // 'valor' => $this->valor ?? null,
        ]);
    }
}
