<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CargaPrecioResource extends JsonResource
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
            'lugar_carga' => $this->lugar_carga,
            'estado' => $this->estado,
            'subido_por' => $this->subido_por,
            'autorizado_por' => $this->autorizado_por,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
