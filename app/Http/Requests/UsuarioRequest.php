<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UsuarioRequest extends FormRequest
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
            'username' => 'required|string|max:150',
            'email' => 'required|email|unique:usuarios,email,' . $this->usuario . ',id',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'nombre' => 'required|max:150',
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
