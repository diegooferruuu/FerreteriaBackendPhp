<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProformaResource extends JsonResource
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
            'codigo_secuencia' => $this->codigo_secuencia,
            'total' =>(float) $this->total,
            'descuento' =>(float) $this->descuento,
            'descuentoMonto' =>(float) $this->descuentoMonto,
            'fecha' => $this->fecha,
            'vigencia' => $this->vigencia,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'detalleProforma' =>  DetalleProformaResource::collection($this->whenLoaded('productos')),
            'sucursal_id' => $this->sucursal_id,
            'sucursal' => SucursalResource::make($this->whenLoaded('sucursal')),
            'cliente_id' => $this->cliente_id,
            'cliente' => ClienteResource::make($this->whenLoaded('cliente')),
            "created_at" => $this->created_at,
            "updated_at" =>$this->updated_at
        ];
    }
}
