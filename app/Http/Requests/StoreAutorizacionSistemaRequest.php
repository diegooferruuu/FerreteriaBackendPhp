<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAutorizacionSistemaRequest extends FormRequest
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
            'nit' => 'required|string|max:20',
            'razon_social' => 'required|string|max:150',
            'nombre_comercial' => 'required|string|max:100',
            'version' => 'required|string|max:5',
            'tipo' => 'required|in:PROPIO,PROVEEDOR',
            'codigo_sistema' => 'required|string|max:50',
            'codigo_ambiente' => 'required|in:1,2',
            'codigo_modalidad' => 'required|in:1,2',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ];
    }
}
