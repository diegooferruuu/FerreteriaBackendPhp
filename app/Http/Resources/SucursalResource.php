<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SucursalResource extends JsonResource
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
            'id'=>$this->id,
            'codigo_siat' => $this->codigo_siat,
            'nombres' => $this->nombres,
            'abreviatura' => $this->abreviatura,
            'direccion' => $this->direccion,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'estado' => $this->estado,
            'departamento_id' => $this->departamento_id,
            'is_offline' => $this->is_offline,
            'in_event' => $this->in_event,
            'cuis' =>$this->cuis ?? '0',
            'sincronizacion' => $this->sincronizacionCatalogo ?? '0',
            $this->mergeWhen($request->has('show_for') && $request->show_for == 'siat',  [
                'cuis' => new CuisResource( $this->whenLoaded('cuis') ),
                'cuis_pos_caducos' => $this->cuis_pos_caducos_count,
                'cufd_pos_caducos' => $this->cufd_pos_caducos_count,
                'facturas_pendientes_recepcion' => $this->facturas_pendientes_recepcion_count,
            ])
        ];
    }
}
