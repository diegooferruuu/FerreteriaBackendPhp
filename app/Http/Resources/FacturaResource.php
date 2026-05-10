<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
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
            'codigo_documento_sector' => $this->codigo_documento_sector,
            'codigo_tipo_factura' => $this->codigo_tipo_factura,
            'numero_documento_identidad' => $this->numero_documento_identidad,
            'codigo_documento_identidad' => $this->codigo_documento_identidad,
            'codigo_metodo_pago' => $this->codigo_metodo_pago,
            'codigo_cliente' => $this->codigo_cliente,
            'razon_social' => $this->razon_social,
            'leyenda' => $this->leyenda,
            'usuario' => $this->usuario,
            'cuf' => $this->cuf,
            'cafc' => $this->cafc,
            'xml' => $this->xml,
            'estado' => $this->estado,
            'venta_id' => $this->venta_id,
            'cufd_id' => $this->cufd_id,
            'venta' => new VentaResource($this->whenLoaded('venta')),
            'cufd' => new CufdResource($this->whenLoaded('cufd')),
        ];
    }
}
