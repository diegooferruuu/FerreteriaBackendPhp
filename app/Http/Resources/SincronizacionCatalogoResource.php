<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SincronizacionCatalogoResource extends JsonResource
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
            'catalogo_facturacion_id' => $this->catalogo_facturacion_id,
            'syncable_id' => $this->syncable_id,
            'syncable_type' => $this->syncable_type,
            'syncable' => $this->syncable,
            'catalogo' => new CatalogoFacturacionResource($this->catalogo),
            'valores' => ValorCatalogoResource::collection($this->valores),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

