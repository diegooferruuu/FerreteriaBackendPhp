<?php

namespace App\Http\Services\Siat;

/**
 * Servicio de recepcion de compras.
 */
class PurchaseReceptionist extends BaseApiSiat
{
    /**
     * Recurso para servicio de recepcion de compras.
     *
     * @var string
     */
    protected $wsdl = 'ServicioRecepcionCompras?wsdl';
    
    /**
     * Funciones disponibles para el servicio de recepcion de compras.
     *
     * @var array
     */
    protected $availableFunctions = [
        'verificarComunicacion', // sin parametros 
        'validacionRecepcionPaqueteCompras' => [
            'params' => [
                'codigoAmbiente',
                'codigoPuntoVenta',
                'codigoSistema',
                'codigoSucursal',
                'cufd',
                'cuis',
                'nit',
                'codigoRecepcion'
            ]
        ],
        'recepcionPaqueteCompras' => [
            'params' => [
                'codigoAmbiente',
                'codigoPuntoVenta',
                'codigoSistema',
                'codigoSucursal',
                'cufd',
                'cuis',
                'nit',
                'archivo',
                'cantidadFacturas',
                'fechaEnvio',
                'gestion',
                'hash',
                'periodo',
            ]
        ],
        'anulacionCompra' => [
            'params' => [
                'SolicitudAnulacionCompra' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'cufd',
                    'cuis',
                    'nit',
                    'codigoAutorizacion',
                    'nitProveedor',
                    'nroDuiDIM', // opcional,
                    'NroFactura',
                ]
            ]
        ],
        'confirmacionCompras' => [
            'params' => [
                'SolicitudConfirmacionCompras' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'cufd',
                    'cuis',
                    'nit',
                    'archivo',
                    'cantidadFacturas',
                    'fechaEnvio',
                    'gestion',
                    'hash',
                    'periodo',
                ]
            ]
        ],
        'consultaCompras' => [
            'SolicitudConsultaCompras' => [
                'codigoAmbiente',
                'codigoPuntoVenta',
                'codigoSistema',
                'codigoSucursal',
                'cufd',
                'cuis',
                'nit',
                'Fecha',
            ]
        ],
    ];

    public function __construct() {
        parent::__construct($this->wsdl, $this->availableFunctions);
    }
}