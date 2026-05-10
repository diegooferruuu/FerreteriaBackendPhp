<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlmacenRequest extends FormRequest
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
            'nombres' => 'required|string|max:150',
            'abreviatura' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:100',
            'latitud' => 'nullable|string|max:30',
            'longitud' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'localidad_id' => 'required|exists:localidades,id_localidad',
            'is_sucursal' => 'required|in:0,1',
        ];
    }
}
