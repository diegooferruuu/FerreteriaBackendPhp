<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CuisResource extends JsonResource
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
            'valor' => $this->valor,
            'validez' => $this->validez,
            'sucursal_id' => $this->sucursal_id,
            'punto_venta_id' => $this->punto_venta_id,
            'sucursal' => new SucursalResource( $this->whenLoaded('sucursal') ),
            'pos' => new PuntoVentaResource( $this->whenLoaded('pos') ),
            'cufd' => new CufdResource( $this->whenLoaded('cufd') ),
            'estado' => $this->estado,
            'estado_validez' => $this->estado_validez,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
