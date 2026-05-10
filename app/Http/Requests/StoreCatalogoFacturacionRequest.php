<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogoFacturacionRequest extends FormRequest
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
            'nombre' => 'required|unique:catalogos_facturacion|string|max:150',
            'servicio_web' => 'required||string|max:50',
            'metodo' => 'required|unique:catalogos_facturacion|string|max:50',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ];
    }
}
