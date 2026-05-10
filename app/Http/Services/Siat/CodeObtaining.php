<?php

namespace App\Http\Services\Siat;

use App\Models\AutorizacionSistema;
use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * Servicio para obtencion de codigos SIAT.
 */
class CodeObtaining extends BaseApiSiat
{
    /**
     * Recurso para servicio de obtencion de codigo.
     *
     * @var string
     */
    protected $wsdl = 'FacturacionCodigos?wsdl';

    /**
     * Funciones disponibles para el servicio de obtencion de codigos.
     *
     * @var array
     */
    protected $availableFunctions = [
        'verificarNit' => [
            'params' => [
                'SolicitudVerificarNit' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'nit',
                    'codigoSucursal',
                    'nitParaVerificacion',
                ]
            ]
        ],
        'verificarComunicacion' => [],
        'cuisMasivo' => [
            'params' => [
                'SolicitudCuisMasivo' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'nit',
                    'datosSolicitud',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ],
            ]
        ],
        'cufd' => [
            'params' => [
                'SolicitudCufd' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ],
            ]
        ],
        'notificaCertificadoRevocado' => [
            'params' => [
                'notificaCertificadoRevocado' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'fechaRevocacion', // opcional,
                    'razonRevocacion',
                    'certificado',
                ],
            ]
        ],
        'cuis' => [
            'params' => [
                'SolicitudCuis' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'nit',
                    'codigoSucursal',
                    'codigoPuntoVenta',//Evniar solo cuando se usa un punto de venta, caso contraro enviar 0
                ],
            ]
        ],
        'cufdMasivo' => [
            'params' => [
                'solicitudCufdMasivo' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'nit',
                    'datosSolicitud',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                    'cuis'
                ]
            ]
        ],
    ];

    /**
     * Datos de autorizacion de sistema necesarios para llamar a funciones del servicio de otencion de codigos.
     *
     * @var AutorizacionSistema instance
     */
    protected $sistema;

    public function __construct()
    {
        parent::__construct($this->wsdl, $this->availableFunctions);
        $this->sistema = AutorizacionSistema::where('estado', 'ACTIVO')->firstOrFail();
    }

    /**
     * Obtener codigo CUIS.
     *
     * @param array $params     Argumentos que contienen el codigo de sucursal y codigo de punto de venta.
     *
     * @return mixed
     */
    public function requestCuis($params) {
//        dd($this->sistema, $params);
        $response = $this->cuis([
            'SolicitudCuis' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit' => $this->sistema->nit,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],//Enviar solo cuando se usa un punto de venta, caso contraro enviar 0
            ]
        ]);

        if( !$response->RespuestaCuis->transaccion && $response->RespuestaCuis->mensajesList->codigo != '980' ) {

            throw new \Exception("CODE: {$response->RespuestaCuis->mensajesList->codigo}, {$response->RespuestaCuis->mensajesList->descripcion}");
        }

        return $response->RespuestaCuis;
    }

    /**
     * Obtener codigo CUFD.
     *
     * @param array $params     Argumentos que contienen el codigo cuis, codigo de sucursal y codigo de punto de venta.
     *
     * @return mixed
     */
    public function requestCufd($params) {

        $response = $this->cufd([
            'SolicitudCufd' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit' => $this->sistema->nit,
                'cuis' => $params['cuis'],// Obtener cuis segun codigo sucursal
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'], // Solo se envía este valor cuando se desea obtener un CUFD para el punto de venta, caso contrario enviar 0
            ]
        ]);

        if( !$response->RespuestaCufd->transaccion ) {
            throw new \Exception("CODE: {$response->RespuestaCufd->mensajesList->codigo}, {$response->RespuestaCufd->mensajesList->descripcion}");
        }
        return $response->RespuestaCufd;
    }

    public function requestVerificacionNit($params)
    {
        $response = $this->verificarNit([
            'SolicitudVerificarNit' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit'=> $this->sistema->nit,
                'cuis'=>$params['valor'],
                'codigoSucursal' => $params['codigo_siat'],
                'nitParaVerificacion' =>$params['cedula_nit'],
            ]
        ]);

        if( !$response->RespuestaVerificarNit->transaccion ) {
            throw new \Exception("CODE: {$response->RespuestaVerificarNit->mensajesList->codigo}, {$response->RespuestaVerificarNit->mensajesList->descripcion}");
        }

        return $response->RespuestaVerificarNit;
//        dd($response);
//        'verificarNit' => [
//        'params' => [
//            'SolicitudVerificarNit' => [
//                'codigoAmbiente',
//                'codigoModalidad',
//                'codigoSistema',
//                'nit',
//                'codigoSucursal',
//                'nitParaVerificacion',
//                ]
//            ]
//        ],


    }
}
