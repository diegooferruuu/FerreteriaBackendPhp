<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
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
            'razon_social' => 'sometimes|required|string|max:150',
            'cedula_nit' => 'sometimes|required|string|max:20',
            'complemento' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'departamento_id' => 'sometimes|required|integer|exists:departamentos,id',
            'tipo_documento_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('valores_catalogo', 'id')->where(function ($query) {
                    return $query->whereExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sincronizacion_catalogos')
                            ->whereColumn('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                            ->where('sincronizacion_catalogos.catalogo_facturacion_id', 6);
                    });
                }),
            ]
        ];
    }
}
