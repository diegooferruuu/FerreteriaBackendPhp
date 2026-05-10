<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PuntoVentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
//        dd(Carbon::now()->format('Y-m-d h:m:s'));
        return [
            'id' => $this->id,
            'tipo_punto_venta_id' => $this->tipo_punto_venta_id,
            'sucursal_id' => $this->sucursal_id,
            'codigo_siat' => $this->codigo_siat,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'is_offline' => $this->is_offline,
            'in_event' => $this->in_event,
            'tipo' => ValorCatalogoResource::make($this->whenLoaded('tipo')),
            'sucursal' => SucursalResource::make($this->whenLoaded('sucursal')),
            'id_evento_significativo' => $this->whenLoaded('eventoSignificativo')?->id ,
            'eventoSignificativo' => $this->whenLoaded('eventoSignificativo'),
            'cufd' => $this->cuis?->cufd ?
                $this->cuis->cufd['validez'] < Carbon::now()->format('Y-m-d h:m:s') ? '0':$this->cuis->cufd
                : '0',
            'id_cuis' => $this->cuis ? $this->cuis->id : null,
            $this->mergeWhen($request->has('show_for') && $request->show_for == 'siat',  [
                'evento' => new EventoSignificativoResource($this->whenLoaded('eventoSignificativo')),
                'cuis' => new CuisResource($this->whenLoaded('cuis')),
                'facturas_pendientes_recepcion' => $this->facturas_pendientes_recepcion_count,
            ])
        ];
    }
}
