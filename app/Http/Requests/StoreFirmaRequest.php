<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreFirmaRequest extends FormRequest
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
//                    dd(mime_content_type($this->certificado->getPathname()));
        return [
            'certificado' => ['required', File::types(['pem', 'crt', 'text','cert','txt'])],
            'llave_privada' => ['required', File::types(['pem', 'crt', 'text','cert','txt'])],
//            'certificado' => 'required|file|mimes:txt,pem,crt',
//            'llave_privada' => 'required|file|mimes:txt,pem,crt',
            'validez' => 'required|date'
        ];
    }
}
