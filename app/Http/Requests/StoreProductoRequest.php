<?php

namespace App\Http\Requests;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreProductoRequest extends FormRequest
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

        if( $this->routeIs('productos.import') ) {
//            dd($this->request);
//            dd(mime_content_type($this->archivo->getPathname()));
            return [
//                    'archivo' => 'required|mimes:text/csv'
                'archivo' => ['required', File::types(['csv', 'xlsx', 'xls','text'])],

//                'archivo' => ['required', 'mimetypes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ];
        }


        return [
            'id' => 'required|unique:productos,id|regex:/^\S+$/',
            'descripcion' => 'required',
            'producto' => 'nullable',
            'imagen' => ['nullable', new Base64Image],
            'codigo_barra' => 'nullable|unique:productos',
            'codigo_qr' => 'nullable|unique:productos',
            'clasificacion_producto_id' => 'required|exists:clasificaciones_producto,id',
            'procedencia_id' => 'required|exists:procedencias,id',
            'unidad_medida' => 'required',
            'codigo_siat' => 'required',
            'precio_menor' => 'bail|numeric|gte:0',
            'precio_mayor' => 'bail|numeric|gte:0',
            'cargaPrecio*.descripcion' => 'required',
            'cargaPrecio*.subido_por' => 'required|integer|exists:usuarios,id',
        ];
    }
}
