<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class InventarioProductoVentaMayorResource extends JsonResource
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
            'cantidad' => $this->cantidad,
            'cantidad_maxima' => $this->cantidad_maxima,
            'cantidad_minima' => $this->cantidad_minima,
            'sucursal_id' => $this->sucursal_id,
            'producto_id' => $this->producto_id,
            'descripcion' => ProductoResource::make($this->producto)->descripcion,
//            'imagen' => $this->producto->imagen==="0" ?  null : URL::to('storage/'.$this->producto->imagen),
            'imagen_url' => $this->producto->imagen ? URL::to(Storage::url($this->producto->imagen)) : URL::to('/noproducto.jpg'),
//            'imagen2' => $this->producto->imagen,
            'codigo_barra' => $this->producto->codigo_barra,
            'unidad' => $this->producto->unidadMedida->unidad_medida,
            'procedencia' => $this->producto->procedencia->procedencia,
            'precio' => $this->producto->precioGeneral->precio_mayor,
            'descuento' => $this->producto->precioGeneral->descuento_mayor,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
