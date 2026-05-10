<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmisionMasivaResource extends JsonResource
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
            'cufd_evento' => $this->cufd_evento,
            'descripcion' => $this->descripcion,
            'fecha_envio' => $this->fecha_envio,
            'estado' => $this->estado,
            'codigo_recepcion' => $this->codigo_recepcion,
            'sucursal_id' => $this->sucursal_id,
            'punto_venta_id' => $this->punto_venta_id,
            'pos' => PuntoVentaResource::make($this->whenLoaded('pos')),
            'facturas' => FacturaResource::collection($this->whenLoaded('facturas')),
            'recepciones' => $this->whenLoaded('recepciones', function () {
                    return $this->recepciones->count();
                }) ?? 0,
            'cantidad_facturas' => $this->whenLoaded('facturas', function () {
                    return $this->facturas->count();
                }) ?? 0,
            "created_at" => $this->created_at,
            "updated_at" =>$this->updated_at
        ];
    }
}
