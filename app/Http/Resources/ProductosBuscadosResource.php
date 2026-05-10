<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProductosBuscadosResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        dd(request()->input('precioMayor'));
        return [
            'id' => $this->id,
            'producto' => $this->producto,
            'descripcion' =>$this->descripcion,
            'imagen_url' => $this->imagen ? URL::to(Storage::url($this->imagen)) : URL::to('/noproducto.jpg'),
            'codigo_barra' => $this->codigo_barra,
            'codigo_qr' => $this->codigo_qr,
            'estado' => $this->estado,
            'clasificacion_producto_id' => $this->clasificacion_producto_id,
            'precio' => request()->input('precioMayor') === "true" ?  $this->precioGeneral->precio_mayor :  $this->precioGeneral->precio_menor,
            'descuento' => request()->input('precioMayor') === "true" ? $this->precioGeneral->descuento_mayor : $this->precioGeneral->descuento_menor,
            'procedencia' => ProcedenciaResource::make($this->procedencia)->procedencia,
            'unidad' =>UnidadMedidaResource::make($this->unidadMedida)->unidad_medida,
            'clasificacionProducto' => $this->clasificacionProducto?->clasificacion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
