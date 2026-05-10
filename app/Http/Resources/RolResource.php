<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RolResource extends JsonResource
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
            'rol' => $this->rol,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'permisos' => $this->whenLoaded('permisos'),
        ];
    }
}
