<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class RolRequest extends FormRequest
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
        if ($this->route()->getName() === 'roles.update' ) {
            return [
                'rol' => 'required|max:50|unique:roles,rol,' . optional($this->route('rol'))->id . ',id',
            ];
        }
        return [
            'rol' => 'required|max:50|unique:roles,rol',
        ];

    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Errores de validación',
            'data'      => $validator->errors()
        ]));
    }
}
