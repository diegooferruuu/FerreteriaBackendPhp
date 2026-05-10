<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreUsuarioRequest extends FormRequest
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
//        regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$/
        return [
            'password' => 'required|string|min:5|confirmed',
            'username' => 'required|string|unique:usuarios,username',
            'email' => 'required|email|unique:usuarios,email',
            'estado' => 'nullable|in:ACTIVO,INACTIVO',
            'nombre' => 'required|max:150',
            'apellidos' => 'nullable|max:150',
            'telefono' => 'nullable|max:30',
            'celular' => 'nullable|max:30',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
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
