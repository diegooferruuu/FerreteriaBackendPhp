<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class AutorizacionSistemaResource extends JsonResource
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
            'nit' => $this->nit,
            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial,
            'version' => $this->version,
            'tipo' => $this->tipo,
            'codigo_sistema' => $this->codigo_sistema,
            'codigo_ambiente' => $this->codigo_ambiente,
            'codigo_modalidad' => $this->codigo_modalidad,
            'logo_url'=>$this->logo ? URL::to(Storage::url($this->logo)) : null,
            'estado' => $this->estado,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
