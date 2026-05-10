<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
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

        if ($this->route()->getName() === 'ventas.store' || $this->route()->getName() === 'proformas.store' || $this->route()->getName() === 'eventos_significativos.transcribe' || $this->route()->getName() === 'emisiones-masivas.transcribe' ) {
            $idCliente = $this->request->all()['cliente_id'];

            return [
                'datosCliente.*razon_social' => 'required',
                'datosCliente.*cedula_nit' => 'required|unique:clientes,cedula_nit,'.$idCliente,
                'datosCliente.*complemento' => 'nullable',
                'datosCliente.*telefono' => 'nullable',
                'datosCliente.*email' => 'nullable',
                'datosCliente.*direccion' => 'nullable',
                'datosCliente.*estado' => 'nullable',
                'datosCliente.*departamento_id' => 'nullable',
                'datosCliente.*tipo_documento_id' => [
                    'required',
                    'integer',
                    Rule::exists('valores_catalogo', 'id')->where(function ($query) {
                        return $query->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('sincronizacion_catalogos')
                                ->whereColumn('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                                ->where('sincronizacion_catalogos.catalogo_facturacion_id', 6);
                        });
                    }),
                ]
            ];

        }

        return [

            'razon_social' => 'required|string|max:150',
            'cedula_nit' => 'required|unique:clientes,cedula_nit|string|max:20',
            'complemento' => 'nullable',
            'telefono' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'departamento_id' => 'required|integer|exists:departamentos,id',
//            'tipo_documento_id' => 'required'
            'tipo_documento_id' => [
                'required',
                'integer',
                Rule::exists('valores_catalogo', 'id')->where(function ($query) {
                    return $query->whereExists(function ($query) {
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
