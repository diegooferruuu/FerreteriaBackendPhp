<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArrayProductoRequest extends FormRequest
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
            'productos' => 'sometimes|required|array',
            'productos.*.id' => 'required_with:productos|distinct|string|exists:productos,id',
            'productos.*.valor' => 'required_with:productos|string|max:150',
            'productos.*.action' => 'required_with:productos|in:sync,detach',
        ];
    }
}
