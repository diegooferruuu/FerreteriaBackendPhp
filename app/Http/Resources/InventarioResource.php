<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventarioResource extends JsonResource
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
            'cantidad' => $this->cantidad,
            'cantidad_maxima' => $this->cantidad_maxima,
            'cantidad_minima' => $this->cantidad_minima,
            'producto_id' => $this->producto_id,
            'sucursal_id' => $this->sucursal_id,
            // 'precio_id' => $this->precio_id,
            'producto' => ( new ProductoResource($this->producto) )->descripcion,
            'sucursal' => ( new SucursalResource($this->sucursal) )->nombres,
//            'precio' => ( new PrecioParticularResource($this->precioParticular) )->precio_venta ?? 0,
//            'descuento' => ( new PrecioParticularResource($this->precioParticular) )->descuento ?? 0,
            'total_inventario_inicial' => $this->total_inventario_inicial ?? "0",
            'total_ingresos' => $this->total_ingresos ?? "0",
            'total_egresos' => $this->total_egresos ?? "0",
            'costo_ingresos' => $this->costo_ingresos ?? "0",
            'costo_egresos' => $this->costo_ingresos ?? "0",
            'inventarioMovimientos' => InventarioMovimientoResource::collection($this->whenLoaded('inventarioMovimientos') ),
            'ingresos' => $this->ingresos,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
