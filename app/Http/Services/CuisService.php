<?php

namespace App\Http\Services;

use App\Http\Services\Siat\CodeObtaining;
use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Support\Arr;

/**
 * Gestion de codigo CUIS.
 */
class CuisService
{
    /**
     * Instancia de servicio de obtencion de codigo.
     *
     * @var CodeObtaining instance
     */
    protected $serviceCode;

    public function __construct()
    {
        $this->serviceCode = new CodeObtaining();
    }

    /**
     * Encargarse de registrar un nuevo CUIS.
     *
     * @param [type] $params
     * @return void
     */
    public function handleStore($params) {

        $posCode = 0;

        if( Arr::exists($params, 'sucursal_id') ) {
            $branchCode = Sucursal::findOrFail($params['sucursal_id'], ['codigo_siat'])->codigo_siat;

        }
        if( Arr::exists($params, 'punto_venta_id') ) {
            $pos = PuntoVenta::with('sucursal:id,codigo_siat')->findOrFail($params['punto_venta_id'], ['sucursal_id', 'codigo_siat']);
//            $test = PuntoVenta::with('sucursal:id_sucursa,codigo_siat')->get();
//            dd($params);
            $branchCode = $pos->sucursal->codigo_siat;
            $posCode = $pos->codigo_siat;

        }

        $response = $this->serviceCode->requestCuis(['branch_code' => $branchCode, 'pos_code' => $posCode]);

        $params = array_merge($params, [
            'valor' => $response->codigo,
            'validez' => $response->fechaVigencia,
        ]);

        $cuis = Cuis::updateOrCreate([
            'valor' => $params['valor']
        ], $params);

        return $cuis;
    }
}
