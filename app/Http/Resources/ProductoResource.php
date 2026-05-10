<?php

namespace App\Http\Resources;

use App\Models\Procedencia;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;

class ProductoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        dd(  $this->whenLoaded('descripcion'));
        return [
            'id' => $this->id,
            'producto' => $this->producto,
            'descripcion' =>$this->descripcion,
            'imagen_url' => $this->imagen ? URL::to(Storage::url($this->imagen)) : URL::to('/noproducto.jpg'),
            'codigo_barra' => $this->codigo_barra,
            'codigo_qr' => $this->codigo_qr,
            'estado' => $this->estado,
            'clasificacion_producto_id' => $this->clasificacion_producto_id,
            'precioGeneral' => new PrecioGeneralResource($this->precioGeneral),
            'procedencia' => ProcedenciaResource::make($this->procedencia),
            'unidad_medida' =>UnidadMedidaResource::make($this->unidadMedida),
            'clasificacionProducto' => $this->clasificacionProducto?->clasificacion,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
