<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CufdResource extends JsonResource
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
            'codigo_control' => $this->codigo_control,
            'validez' => $this->validez,
            'cuis_id' => $this->cuis_id,
            'cuis' => new CuisResource( $this->whenLoaded('cuis') ),
            'estado' => $this->estado,
            'estado_validez' => $this->estado_validez,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
