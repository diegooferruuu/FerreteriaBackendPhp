<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomologacionProductoResource extends JsonResource
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
            $this->mergeWhen($request->routeIs('homologacion_productos.index'),  [
                'id' => $this->id,
                'id_homologacion_producto' => $this->homologacion?->id,
                'codigo_alternativo' => $this->codigo_alternativo,
                'producto' => $this->producto,
                'descripcion' => $this->descripcion,
                'imagen' => $this->imagen ? asset('storage/' . $this->imagen) : null,
                'estado_homologacion' => $this->homologacion ? 'HOMOLOGADO' : 'PENDIENTE',
                'codigo_siat' => $this->homologacion?->codigo_siat,
                'codigo_actividad' => $this->homologacion?->catalogoProducto->codigo_actividad,
                'producto_siat' => $this->homologacion?->catalogoProducto->descripcion,
                'producto_created_at' => $this->created_at,
                'producto_updated_at' => $this->updated_at,
                'homologacion_created_at' => $this->homologacion?->created_at,
                'homologacion_updated_at' => $this->homologacion?->updated_at,
            ]),
            $this->mergeWhen($request->routeIs('homologacion_productos.show'), [
                'id' => $this->id,
                'producto_id' => $this->producto_id,
                'catalogo_producto_id' => $this->catalogo_producto_id,
                'codigo_siat' => $this->codigo_siat,
                'estado' => $this->estado,
                'producto' => new ProductoResource($this->producto),
                'catalogoProductoSiat' => new ValorCatalogoResource($this->catalogoProducto),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ])
        ];
    }
}
