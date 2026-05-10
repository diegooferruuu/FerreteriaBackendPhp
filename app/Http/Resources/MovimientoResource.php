<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MovimientoResource extends JsonResource
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
            'movimiento' => $this->movimiento,
            'abreviatura' => $this->abreviatura,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
