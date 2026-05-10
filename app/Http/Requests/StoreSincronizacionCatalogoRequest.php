<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSincronizacionCatalogoRequest extends FormRequest
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
            'catalogo_facturacion_id' => 'integer|exists:catalogos_facturacion,id',
            'syncable_type' => 'required|string|in:sucursal,pos',
            'syncable_id' => [
                'required',
                'integer',
                Rule::exists($this->syncable_type == 'sucursal' ? 'sucursales' : 'puntos_venta', $this->syncable_type == 'sucursal' ? 'id' : 'id'),
            ]
        ];
    }
}
