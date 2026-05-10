<?php

namespace App\Http\Services\Siat;

use App\Models\AutorizacionSistema;
use SoapClient;

/**
 * Servicio de sincronizacionCatalogo de datos SIAT.
 */
class DataSync extends BaseApiSiat
{
    /**
     * Recurso para servicio de sincronizacionCatalogo de datos.
     *
     * @var string
     */
    protected $wsdl = 'FacturacionSincronizacion?wsdl';

    /**
     * Funciones disponibles para el servicio de sincronizacionCatalogo de datos.
     *
     * @var array
     */
    protected $availableFunctions = [
        'sincronizarParametricaMotivoAnulacion' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarActividades' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarFechaHora' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarListaLeyendasFactura' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoHabitacion' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarListaActividadesDocumentoSector' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoDocumentoIdentidad' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaUnidadMedida' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoDocumentoSector' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTiposFactura' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'verificarComunicacion' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarListaMensajesServicios' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoMetodoPago' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaEventosSignificativos' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoPuntoVenta' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarListaProductosServicios' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoEmision' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaPaisOrigen' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
        'sincronizarParametricaTipoMoneda' => [
            'params' => [
                'SolicitudSincronizacion' => [
                    'codigoAmbiente',
                    'codigoSistema',
                    'nit',
                    'cuis',
                    'codigoSucursal',
                    'codigoPuntoVenta',
                ]
            ]
        ],
    ];

    /**
     * Datos de autorizacion de sistema necesarios para llamar a funciones del servicio de sincronizacionCatalogo de datos.
     *
     * @var AutorizacionSistema instance
     *
     * @todo Llevar la variable a BaseApiSiat class.
     */
    protected $sistema;

    public function __construct() {
        parent::__construct($this->wsdl, $this->availableFunctions);
        $this->sistema = AutorizacionSistema::where('estado', 'ACTIVO')->firstOrFail();
    }

    /**
     * Formatear respuesta obtenida desde servicio web.
     *
     * @param mixed $response
     * @return array
     */
    protected function formatResponse($response) {
        $responseBody = $response->RespuestaListaParametricas ??
            $response->RespuestaListaActividades ??
            // $response->RespuestaFechaHora ??
            $response->RespuestaListaParametricasLeyendas ??
            $response->RespuestaListaActividadesDocumentoSector ??
            $response->RespuestaListaProductos;

        $data = $responseBody->mensajesList ??
            $responseBody->listaCodigos ??
            $responseBody->listaActividades ??
            // $responseBody->fechaHora ??
            $responseBody->listaLeyendas ??
            $responseBody->listaActividadesDocumentoSector;

        return [
            'transaccion' => $responseBody->transaccion,
            'data' => $data
        ];
    }

    /**
     * Sincronizar fecha y hora
     *
     * @param array $params     Argumentos que contienen codigo de sucursal y codigo de punto de venta
     *
     * @return mixed
     */
    protected function syncDateTime($params) {
        $response = $this->{'sincronizarFechaHora'}([
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit' => $this->sistema->nit,
                'cuis' => $params['cuis'],
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
            ]
        ]);
        return $response;
    }

    /**
     * Sincronizar catalogo.
     *
     * @param string $method    Nombre de la function para sincronizar el catalogo.
     * @param array $params     Argumentos que contienen codigo CUIS, codigo de sucursal y codigo de punto de venta.
     *
     * @return mixed
     */
    public function syncCatalog($method, $params) {

        $response = $this->{$method}([
            'SolicitudSincronizacion' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'nit' => $this->sistema->nit,
                'cuis' => $params['cuis'],
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
            ]
        ]);

        $response = $this->formatResponse($response);

        if( !$response['transaccion'] ) {
            throw new \Exception("CODE:{$response['data']->codigo}, {$response['data']->descripcion}");
        }

        return $response;
    }
}
