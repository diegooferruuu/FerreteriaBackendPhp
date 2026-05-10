<?php

namespace App\Http\Requests;

use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreEventoSignificativoRequest extends FormRequest
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
            'cafc' => [
                Rule::requiredIf(function() {
                    return ValorCatalogo::catalogo(12)
                        ->where('id', $this->evento_id)
                        ->whereIn('codigo_clasificador', [5,6,7])->exists();
                }),
                'string',
                'max:100',
            ],
            'descripcion' => 'required|string|max:150',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => [
                Rule::requiredIf(function() {
                    return ValorCatalogo::catalogo(12)
                        ->where('id', $this->evento_id)
                        ->whereIn('codigo_clasificador', [5,6,7])->exists();
                }),
                'date',
                'after:fecha_inicio',
            ],
            'evento_id' => [
                'required',
                'integer',
                Rule::exists('valores_catalogo', 'id')->where(function($query) {
                    return $query->whereExists(function($query) {
                        $query->select(DB::raw(1))
                            ->from('sincronizacion_catalogos')
                            ->whereColumn('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                            ->where('sincronizacion_catalogos.catalogo_facturacion_id', 12);
                    });
                }),
            ],
            'sucursal_id' => 'required_without:punto_venta_id|prohibits:punto_venta_id|integer|exists:sucursales,id',
            'punto_venta_id' => 'required_without:sucursal_id|prohibits:sucursal_id|integer|exists:puntos_venta,id',
            'cufd_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !DB::table('cufd')->where('id', $value)->exists()) {
                        $fail('El campo '.$attribute.' no existe.');
                    }
                }
            ],
//            'estado' => 'in:INICIADO,FINALIZADO',
        ];
    }

    /**
    * Prepare the data for validation.
    *
    * @return void
    */
    protected function prepareForValidation()
    {
//        $this->merge([
//            'estado' => !is_null($this->fecha_fin) ? 'FINALIZADO' : 'INICIADO',
//        ]);
    }
}
