<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocalidadResource extends JsonResource
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
            'id' => $this->id_localidad,
            'localidad' => $this->localidad,
            'municipio_id' => $this->municipio_id,
            'municipio' => new MunicipioResource( $this->whenLoaded('municipio') ),
            'datosProveedores' => DatoProveedorResource::collection( $this->whenLoaded('datosProveedores') ),
            'almacenes' => AlmacenResource::collection( $this->whenLoaded('almacenes') ),
            'sucursales' => SucursalResource::collection( $this->whenLoaded('sucursales') ),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
