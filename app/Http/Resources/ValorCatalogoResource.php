<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ValorCatalogoResource extends JsonResource
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
            'sincronizacion_catalogo_id' => $this->sincronizacion_catalogo_id,
            'codigo_clasificador' => $this->codigo_clasificador,
            'codigo_actividad' => $this->codigo_actividad,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'catalogo' => $this->sincronizacion->catalogo->nombre ?? null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
