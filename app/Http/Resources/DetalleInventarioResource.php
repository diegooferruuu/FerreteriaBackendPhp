<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetalleInventarioResource extends JsonResource
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
            'cantidad' =>(float)$this->pivot->cantidad,
            'precio' =>(float)$this->pivot->precio,
            'descuento' =>(float)$this->pivot->descuento,
            'sub_total' =>(float)$this->pivot->sub_total,
            'inventario_id' => $this->pivot->inventario_id,
            'producto_id'=>$this->producto_id,
        ];
    }
}
