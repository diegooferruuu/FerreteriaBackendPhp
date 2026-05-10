<?php

namespace App\Http\Services;

use App\Http\Services\Siat\CodeObtaining;
use App\Models\Cufd;
use App\Models\Cuis;

/**
 * Gestion de codigo CUFD
 */
class CufdService
{
    /**
     * instancia de servicio de obtencion de codigo
     *
     * @var CodeObtaining instance
     */
    protected $serviceCode;

    public function __construct()
    {
        $this->serviceCode = new CodeObtaining();
    }

    /**
     * Encargarse de registrar un nuevo CUFD
     *
     * @param [type] $params
     * @return void
     */
    public function handleStore($params) {


        $cuis = Cuis::with([
            'sucursal:id,codigo_siat',
            'pos:id,sucursal_id,codigo_siat' => ['sucursal:id,codigo_siat']
        ])->findOrFail($params['cuis_id'], ['id', 'valor', 'sucursal_id', 'punto_venta_id']);

        if( !is_null($cuis->sucursal_id) ) {
            $branchCode = $cuis->sucursal->codigo_siat;
            $posCode = 0;
        }

        if( !is_null($cuis->punto_venta_id) ) {
            $branchCode = $cuis->pos->sucursal->codigo_siat;
            $posCode = $cuis->pos->codigo_siat;
        }
        try {
            $response = $this->serviceCode->requestCufd(['cuis' => $cuis->valor, 'branch_code' => $branchCode, 'pos_code' => $posCode]);
        }catch (\Throwable $error) {
            throw new \Exception("{$error->getMessage()}", $error->getCode());
        }

        $params = array_merge($params, [
            'valor' => $response->codigo,
            'codigo_control' => $response->codigoControl,
            'validez' => $response->fechaVigencia,
        ]);

        Cufd::where(['estado' => 'ACTIVO', 'cuis_id' => $params['cuis_id']])->update(['estado' => 'INACTIVO']);

        $cufd = Cufd::create($params);

        return $cufd;
    }
}
