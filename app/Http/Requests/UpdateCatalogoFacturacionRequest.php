<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCatalogoFacturacionRequest extends FormRequest
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
            'nombre' => ['sometimes', 'required', 'string', 'max:150', Rule::unique('catalogos_facturacion')->ignore($this->catalogoFacturacion, 'id')],
            'servicio_web' => 'sometimes|required||string|max:50',
            'metodo' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('catalogos_facturacion')->ignore($this->catalogoFacturacion, 'id')],
            'estado' => 'sometimes|required|in:ACTIVO,INACTIVO',
        ];
    }
}
