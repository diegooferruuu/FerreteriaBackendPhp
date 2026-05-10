<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpdateCafcRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        if ($this->route()->getName() === 'cafc.update' ) {
            return [
                'cafc'=>'required|unique:cafc,cafc,'.$this->id.',id',
                'numero_inicio'=>'required|numeric',
                'numero_fin'=>'required|numeric',
                'sucursal_id' => 'required|exists:sucursales,id',
            ];
        }
        return [
            'cafc'=>'required|unique:cafc,cafc',
            'numero_inicio'=>'required|numeric',
            'numero_fin'=>'required|numeric',
            'sucursal_id' => 'required|exists:sucursales,id',
        ];
    }
}
