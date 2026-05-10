<?php

namespace App\Http\Resources;

use App\Models\AutorizacionSistema;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $autorizacionSistema = AutorizacionSistema::first();
//        dd($autorizacionSistema);
        return [
            'codigo_secuencia' => $this->codigo_secuencia,
            'total' => $this->total,
            'descuento' => $this->descuento,
            'fecha' => $this->fecha,
            'descripcion' => $this->descripcion,
            'informacion_tarjeta' => $this->informacion_tarjeta,
            'estado' => $this->estado,
            'metodo_pago_id' => $this->metodo_pago_id,
            'sucursal_id' => $this->sucursal_id,
            'cliente_id' => $this->cliente_id,
            'tipo_venta_id' => $this->tipo_venta_id,
            'punto_venta_id' => $this->punto_venta_id,
            'nit' => $autorizacionSistema->nit,
            'factura' => FacturaResource::make($this->factura)

        ];
    }
}
