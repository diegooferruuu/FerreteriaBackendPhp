<?php

namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
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
            'descripcion' => 'required',
            'producto' => 'nullable',
            'imagen' => ['nullable', new Base64Image],
            'codigo_barra' => 'nullable',
            'codigo_qr' => 'nullable',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'clasificacion_producto_id' => 'required|exists:clasificaciones_producto,id',
            'procedencia_id' => 'required|exists:procedencias,id',
            'unidad_medida' => 'required',
            'codigo_siat' => 'required',
            'precio_menor' => 'bail|numeric|gte:0',
            'precio_mayor' => 'bail|numeric|gte:0',
            'cargaPrecio*.descripcion' => 'required',
            'cargaPrecio*.subido_por' => 'required|integer|exists:usuarios,id',
        ];
    }
}
