<?php

namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAutorizacionSistemaRequest extends FormRequest
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
//            'nit' => 'sometimes|required|string|max:20',
//            'razon_social' => 'sometimes|required|string|max:150',
            'nombre_comercial' => 'sometimes|required|string|max:100',
            'version' => 'sometimes|required|string|max:5',
//            'tipo' => 'sometimes|required|in:PROPIO,PROVEEDOR',
            'codigo_sistema' => 'sometimes|required|string|max:50',
            'codigo_ambiente' => 'sometimes|required|in:1,2',
            'logo' => ['nullable', new Base64Image],
//            'codigo_modalidad' => 'sometimes|required|in:1,2',
//            'estado' => 'sometimes|required|in:ACTIVO,INACTIVO',
        ];
    }
}
