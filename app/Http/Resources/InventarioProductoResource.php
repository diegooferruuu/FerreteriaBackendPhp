<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventarioProductoResource extends JsonResource
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
            'unidad' => $this->producto->atributos->filter(function($value, $key){
                return $value->atributo === "Unidad";
            })->count() > 0 ?  $this->producto->atributos->filter(function($value, $key){
                return $value->atributo === "Unidad";
            })->values()[0]->pivot->valor : "",
            'precio' =>$this->precioParticular,
//            'precio_particular_id' => $this->precio_particular_id,

            'precio' =>  $this->precioParticular === null ?
                (float) $this->producto->precioGeneral->precio_compra : (float)$this->precioParticular->precio_compra,
            'descuento' =>  $this->precioParticular === null ?
                (float) $this->producto->precioGeneral->descuento_compra : (float)$this->precioParticular->descuento_compra,

            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at
        ];
    }
}
