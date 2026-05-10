<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetalleProformaResource extends JsonResource
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
            'cantidad' =>(float)$this->pivot->cantidad,
            'precio' =>(float)$this->pivot->precio,
            'descuento' =>$this->pivot->descuento,
            'descuentoMonto' =>(float)$this->pivot->descuentoMonto,
            'sub_total' =>(float)$this->pivot->sub_total,
            'descripcion' => $this->descripcion,
            'procedencia' => $this->procedencia->procedencia,
            'unidad' => $this->unidadMedida->unidad_medida,
            'producto_id'=>$this->pivot->producto_id,
            'codigo_producto_mayor_menor'=>$this->pivot->codigo_producto_mayor_menor,
            'codigoProductoMayorMenor'=>$this->pivot->codigo_producto_mayor_menor
        ];

    }
}
