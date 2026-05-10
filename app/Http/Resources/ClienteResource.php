<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'departamento_id' => $this->departamento_id,
            'tipo_documento_id' => $this->tipo_documento_id,
            'razon_social' => $this->razon_social,
            'cedula_nit' => $this->cedula_nit,
            'complemento' => $this->complemento,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'verificacion' => $this->verificacion,
            'estado' => $this->estado,
            'tipoDocumento' =>  ValorCatalogoResource::make( $this->whenLoaded('tipoDocumento') ),
            'tipo_documento_descripcion' => $this->when( $request->routeIs('clientes.index'), $this->tipoDocumento?->descripcion),
            'clienteTipoVentas' => $this->clienteTipoVentas,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
