<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventoSignificativoResource extends JsonResource
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
            'evento_id' => $this->evento_id,
            'sucursal_id' => $this->sucursal_id,
            'punto_venta_id' => $this->punto_venta_id,
            'cufd_evento' => $this->cufd_evento,
            'cafc' => $this->cafc,
            'descripcion' => $this->descripcion,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'estado' => $this->estado,
            'codigo_recepcion' => $this->codigo_recepcion,
            'codigo_evento_siat' => $this->codigo_evento_siat ?? null,
            'evento_siat' => $this->descripcion__evento_siat ?? null,
            'facturas' => FacturaResource::collection($this->whenLoaded('facturas')),
            'recepciones' => $this->whenLoaded('recepciones', function () {
                    return $this->recepciones->count();
                }) ?? 0,
            'cantidad_facturas' => $this->whenLoaded('facturas', function () {
                    return $this->facturas->count();
                }) ?? 0,
        ];
    }
}
