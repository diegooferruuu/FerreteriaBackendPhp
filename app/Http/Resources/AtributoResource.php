<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AtributoResource extends JsonResource
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
            'atributo' => $this->atributo,
            // 'pivot' => $this->whenNotNull($this->pivot),
            'valor' => $this->whenPivotLoaded('producto_atributo', function () {
                return $this->pivot->valor;
            }),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
