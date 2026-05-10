<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrecioGeneralResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'producto_id' => $this->producto_id,
            'carga_precio_id' => $this->carga_precio_id,
            'precio_menor' => $this->precio_menor,
            'descuento_menor' => $this->descuento_menor,
            'precio_mayor' => $this->precio_mayor,
            'descuento_mayor' => $this->descuento_mayor,
            'producto' => new ProductoResource($this->whenLoaded('producto')),
            'cargaPrecio' => new CargaPrecioResource($this->whenLoaded('autorizador')),
            'estado' => $this->estado,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
