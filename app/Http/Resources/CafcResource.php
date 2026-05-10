<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CafcResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
           "id" => $this->id,
           "cafc" => $this->cafc,
           "numero_inicio" => $this->numero_inicio,
           "numero_fin" => $this->numero_fin,
           "numero_facturas_utilizadas" => $this->numero_facturas_utilizadas,
           "estado" => $this->estado,
           "sucursal_id" => $this->sucursal_id,
           "created_at" => $this->created_at,
           "updated_at" =>$this->updated_at
       ];
    }
}
