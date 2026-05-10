<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnidadMedidaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'unidad_medida' => $this->unidad_medida,
            'codigo_siat' => $this->valor_catalogo_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
