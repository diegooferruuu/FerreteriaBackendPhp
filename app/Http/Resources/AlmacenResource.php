<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AlmacenResource extends JsonResource
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
            'id' => $this->id_almacen,
            'nombres' => $this->nombres,
            'abreviatura' => $this->abreviatura,
            'direccion' => $this->direccion,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'telefono' => $this->telefono,
            'estado' => $this->estado,
            'localidad_id' => $this->localidad_id,
            'sucursal' => new SucursalResource($this->sucursal),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
