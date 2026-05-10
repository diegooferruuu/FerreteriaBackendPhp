<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends FormRequest
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
//            'codigo_siat' => [
//                'sometimes',
//                'integer',
//                Rule::unique('sucursales')->ignore($this->sucursal, 'id')
//            ],
            'nombres' => 'sometimes|required|string|max:150',
            'abreviatura' => 'nullable|string|max:20',
            'direccion' => 'sometimes|required|string|max:100',
            'latitud' => 'nullable|string|max:30',
            'longitud' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'estado' => 'sometimes|required|in:ACTIVO,INACTIVO',
            'departamento_id' => 'sometimes|required|exists:departamentos,id',
        ];
    }
}
