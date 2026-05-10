<?php

namespace App\Http\Services\Siat;

use App\Models\AutorizacionSistema;
use App\Models\EventoSignificativo;
use DateTime;
use Illuminate\Http\Request;

/**
 * Servicio de operaciones.
 */
class Operation extends BaseApiSiat
{
    /**
     * Recurso para servicio de operaciones.
     *
     * @var string
     */
    protected $wsdl = 'FacturacionOperaciones?wsdl';

    /**
     * Datos de autorizacion de sistema necesarios para llamar a funciones del servicio de operaciones.
     *
     * @var AutorizacionSistema instance
     */
    protected $sistema;

    /**
     * Funciones disponibles para el servicio de operaciones.
     *
     * @var array
     */
    protected $availableFunctions = [
        'verificarComunicacion' => [],
        'registroPuntoVenta' => [
            'params' => [
                'SolicitudRegistroPuntoVenta' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'codigoSucursal',
                    'codigoTipoPuntoVenta',
                    'cuis',
                    'descripcion',
                    'nit',
                    'nombrePuntoVenta',
                ]
            ]
        ],
        'registroPuntoVentaComisionista' => [
            'params' => [
                'SolicitudPuntoVentaComisionista' => [
                    'codigoAmbiente',
                    'codigoModalidad',
                    'codigoSistema',
                    'codigoSucursal',
                    'cuis',
                    'descripcion',
                    'fechaFin',
                    'fechaFin',
                    'nit',
                    'nitComisionista',
                    'nombrePuntoVenta',
                    'numeroContrato',
                ]
            ]
        ],
        'cierreOperacionesSistema' => [
            'params' => [
                'SolicitudOperaciones' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'codigoModalidad',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'consultaEventoSignificativo' => [
            'params' => [
                'SolicitudConsultaEvento' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'cufd',
                    'codigoSucursal',
                    'codigoPuntoVenta',// 0
                    'fechaEvento'
                ]
            ]
        ],
        'consultaPuntoVenta' => [
            'params' => [
                'SolicitudConsultaPuntoVenta' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'codigoSucursal',
                    'cuis',
                    'nit',
                ]
            ]
        ],
        'registroEventoSignificativo' => [
            'params' => [
                'SolicitudEventoSignificativo' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'cufd',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                    'codigoEvento',
                    'descripcion',
                    'fechaInicioEvento',
                    'fechaFinEvento',
                    'cufdEvento',
                ]
            ]
        ],
        'cierrePuntoVenta' => [
            'params' => [
                'codigoAmbiente',
                'codigoPuntoVenta',
                'codigoSistema',
                'codigoSucursal',
                'cuis',
                'nit'
            ]
        ]
    ];

    public function __construct() {
        parent::__construct($this->wsdl, $this->availableFunctions);
        $this->sistema = AutorizacionSistema::where('estado', 'ACTIVO')->firstOrFail();
    }

    /**
     * Formatear respuesta optenida desde servicio web.
     *
     * @param mixed $response
     * @return array
     */
    protected function formatResponse($response) {
        return [
            'transaccion' => $response->RespuestaConsultaPuntoVenta->transaccion,
            'data' => $response->RespuestaConsultaPuntoVenta->mensajesList ?? $response->RespuestaConsultaPuntoVenta->listaPuntosVentas,
        ];
    }

    public function getEvent($params){

        $response = $this->consultaEventoSignificativo([
            'SolicitudConsultaEvento' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,//obtener desde DB
                'nit' => $this->sistema->nit,
                'cuis' => $params['cuis'],//Cuis de la sucursal o punto de venta
                'cufd' => $params['cufd'],//Cufd de la sucursal o punto de venta solicitada una vez recuperado del evento
                'codigoSucursal' => $params['branch_code'],
                'fechaEvento' => $params['fecha_inicio'],
            ]
        ]);
      return $response->RespuestaListaEventos;

    }


    /**
     * Registrar evento significativo.
     *
     * @param array $params     Argumentos necesarios para llamar a la function.
     * @return mixed
     */
    public function registerEvent($params) {

        $response = $this->registroEventoSignificativo([
            'SolicitudEventoSignificativo' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,//obtener desde DB
                'nit' => $this->sistema->nit,
                'cuis' => $params['cuis'],//Cuis de la sucursal o punto de venta
                'cufd' => $params['cufd'],//Cufd de la sucursal o punto de venta solicitada una vez recuperado del evento
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoMotivoEvento' => $params['event_code'],
                'descripcion' => $params['descripcion'],
                'fechaHoraInicioEvento' => $params['fecha_inicio'],
                'fechaHoraFinEvento' => $params['fecha_fin'],
                'cufdEvento' => $params['cufd_evento'], //Cufd que se tenia durante el evento
            ]
        ]);

        return $response->RespuestaListaEventos;
    }


//    public function registerEvent($params) {
//
//        $response = $this->registroEventoSignificativo([
//            'SolicitudEventoSignificativo' => [
//                'codigoAmbiente' => $this->sistema->codigo_ambiente,
//                'codigoSistema' => $this->sistema->codigo_sistema,//obtener desde DB
//                'nit' => $this->sistema->nit,
//                'cuis' => $params['cuis'],//Cuis de la sucursal o punto de venta
//                'cufd' => $params['cufd'],//Cufd de la sucursal o punto de venta solicitada una vez recuperado del evento
//                'codigoSucursal' => $params['branch_code'],
//                'codigoPuntoVenta' => $params['pos_code'],
//                'codigoMotivoEvento' => $params['event_code'],
//                'descripcion' => $params['descripcion'],
//                'fechaHoraInicioEvento' => $params['fecha_inicio'],
//                'fechaHoraFinEvento' => $params['fecha_fin'],
//                'cufdEvento' => $params['cufd_evento'], //Cufd que se tenia durante el evento
//            ]
//        ]);
//        return $response->RespuestaListaEventos;
//    }
}
