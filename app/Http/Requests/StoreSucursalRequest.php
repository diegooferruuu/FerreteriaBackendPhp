<?php

namespace App\Http\Requests;

use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Http\FormRequest;

class StoreSucursalRequest extends FormRequest
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
            'codigo_siat' => 'required|integer|unique:sucursales',
            'nombres' => 'required|string|max:150',
            'abreviatura' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:100',
            'latitud' => 'nullable|string|max:30',
            'longitud' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'departamento_id' => 'required|exists:departamentos,id',
        ];
    }
}
