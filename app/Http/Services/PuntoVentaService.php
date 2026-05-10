<?php

namespace App\Http\Services;

use App\Http\Services\Siat\Operation;
use App\Models\AutorizacionSistema;
use App\Models\Cuis;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;

/**
 * Gestion de punto de venta.
 */
class PuntoVentaService
{
    /**
     * Instancia de servicio de operaciones.
     *
     * @var Operation
     */
    protected $serviceOperation;

    /**
     * Datos de autorizacion de sistema.
     *
     * @var AutorizacionSistema
     */
    protected $sistema;

    public function __construct()
    {
        $this->serviceOperation = new Operation();
        $this->sistema = AutorizacionSistema::where('estado', 'ACTIVO')->firstOrFail();
    }

    /**
     * Registrar punto de venta en SIAT.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return void
     */
    public function register(array $params)
    {
        $posTypeCode = ValorCatalogo::findOrFail($params['tipo_punto_venta_id'], ['codigo_clasificador'])->codigo_clasificador;
        $sucursal = Sucursal::findOrFail($params['sucursal_id'], ['id', 'codigo_siat']);

        if( !$sucursal->cuis ) {
            throw new \Exception("Establece un CUIS para la sucursal");
        }

        $branchCode = $sucursal->codigo_siat;
        $cuis = $sucursal->cuis->valor;

        $response = $this->serviceOperation->registroPuntoVenta([
            'SolicitudRegistroPuntoVenta' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoModalidad' => $this->sistema->codigo_sistema,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoSucursal' => $branchCode,
                'codigoTipoPuntoVenta' => $posTypeCode,
                'cuis' => $cuis,
                'descripcion' => $params['descripcion'],
                'nit' => $this->sistema->nit,
                'nombrePuntoVenta' => $params['nombre'],
            ]
        ]);

        if( !$response->RespuestaRegistroPuntoVenta->transaccion ) {
            throw new \Exception("CODE:{$response->RespuestaRegistroPuntoVenta->mensajesList->codigo}, {$response->RespuestaRegistroPuntoVenta->mensajesList->descripcion}");
        }

        return $response;
    }

    /**
     * Obtener puntos de venta registrados en SIAT.
     *
     * @param Sucursal $sucursal
     * @return mixed
     */
    public function getRegistered(Sucursal $sucursal)
    {
        if( !$sucursal->cuis ) {
            throw new \Exception("Establece un CUIS para la sucursal");
        }

        $response = $this->serviceOperation->consultaPuntoVenta([
            'SolicitudConsultaPuntoVenta' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $sucursal->codigo_siat,
                'cuis' => $sucursal->cuis->valor,
            ]
        ]);

        $response = $this->serviceOperation->formatResponse($response);

        if( !$response['transaccion'] ) {
            throw new \Exception("CODE: {$response['data']->codigo}, {$response['data']->descripcion}");
        }
        return $response;
    }

    public function destroy(Sucursal $sucursal)
    {
        if( !$sucursal->cuis ) {
            throw new \Exception("Establece un CUIS para la sucursal");
        }
        $response = $this->serviceOperation->consultaPuntoVenta([
            'CierrePuntoventa' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoPuntoVenta' => '',
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoSucursal' => $sucursal->codigo_siat,
                'cuis' => $sucursal->cuis->valor,
                'nit' => $this->sistema->nit,
            ]
        ]);

        $response = $this->serviceOperation->formatResponse($response);

        if( !$response['transaccion'] ) {
            throw new \Exception("CODE: {$response['data']->codigo}, {$response['data']->descripcion}");
        }
        return $response;
    }
}
