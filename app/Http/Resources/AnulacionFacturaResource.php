<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AnulacionFacturaResource extends JsonResource
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
            'factura_id' => $this->factura_id,
            'motivo_id' => $this->motivo_id,
            'descripcion' => $this->descripcion,
            'codigo_estado' => $this->codigo_estado,
            'codigo_recepcion' => $this->codigo_recepcion,
            'codigo_descripcion' => $this->codigo_descripcion,
        ];
    }
}
