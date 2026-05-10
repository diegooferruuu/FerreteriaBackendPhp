<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventarioMovimientoResource extends JsonResource
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
            'inicial' => $this->inicial,
            'ingresos' => $this->ingresos,
            'egresos' => $this->egresos,
            'saldo' =>  $this->whenNotNull($this->saldo),
            'precio' => $this->precio,
            'identificador' => $this->identificador,
            'origen' => $this->origen,
            'secuencial_origen' => $this->secuencial_origen,
            'fecha' => $this->fecha,
            'fecha_vencimiento' => $this->fecha_vencimiento,
            'movimiento_id' => $this->movimiento_id,
            'inventario_id' => $this->inventario_id,
            'movimiento' => ( new MovimientoResource($this->movimiento) )->movimiento,
        ];
    }
}
