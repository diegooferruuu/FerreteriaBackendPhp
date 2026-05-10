<?php

namespace App\Http\Services\Siat;

use App\Models\AutorizacionSistema;

/**
 * Servicio de facturacion de compra y venta.
 */
class SalePurchaseInvoice extends BaseApiSiat
{
    /**
     * Recurso para servicio de facturacion de compra y venta.
     *
     * @var string
     */
    protected $wsdl = 'ServicioFacturacionCompraVenta?wsdl';

    /**
     * Datos de autorizacion de sistema necesarios para llamar a funciones del servicio de operaciones.
     *
     * @var AutorizacionSistema instance
     */
    protected $sistema;

    /**
     * Funciones disponibles para el servicio de facturacion de compra y venta.
     *
     * @var array
     */
    protected $availableFunctions = [
        'verificarComunicacion' => [], // no requiere token?
        'recepcionFactura' => [
            'params' => [
                'SolicitudServicioRecepcionFactura' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'archivo',// alfanumerico?
                    'fechaEnvio',
                    'hashArchivo'
                ]
            ]
        ],
        'recepcionPaqueteFactura' => [
            'params' => [
                'SolicitudServicioRecepcionPaquete' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'archivo',
                    'fechaEnvio',
                    'hashArchivo',
                    'cafc',// null
                    'cantidadFacturas',
                    'codigoEvento',// Codigo que devolvio al registrar el evento
                ]
            ]
        ],
        'recepcionMasivaFactura' => [
            'params' => [
                'SolicitudServicioRecepcionMasiva' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'archivo',
                    'fechaEnvio',
                    'hashArchivo',
                    'cantidadFacturas',
                ]
            ]
        ],
        'recepcionAnexos' => [
            'params' => [
                'codigoAmbiente',
                'codigoDocumentoSector',
                'codigoEmision',
                'codigoModalidad',
                'codigoPuntoVenta',
                'codigoSistema',
                'codigoSucursal',
                'cufd',
                'cuis',
                'nit',
                'tipoFacturaDocumento',
                'codigo',
                'codigoProducto',
                'codigoProductoSin',
                'tipoCodigo',
                'cuf'
            ]
        ],
        'validacionRecepcionMasivaFactura' => [
            'params' => [
                'SolicitudServicioValidacionRecepcionMasiva' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'codigoRecepcion'
                ]
            ]
        ],
        'validacionRecepcionPaqueteFactura' => [
            'params' => [
                'SolicitudServicioValidacionRecepcionPaquete' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'codigoRecepcion',
                ]
            ]
        ],
        'verificacionEstadoFactura' => [
            'params' => [
                'SolicitudServicioVerificacionEstadoFactura' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'cuf'
                ]
            ]
        ],
        'anulacionFactura' => [
            'params' => [
                'SolicitudServicioAnulacionFactura' => [
                    'codigoAmbiente',
                    'codigoPuntoVenta',
                    'codigoSistema',
                    'codigoSucursal',
                    'nit',
                    'codigoDocumentoSector',
                    'codigoEmision',
                    'codigoModalidad',
                    'cufd',
                    'cuis',
                    'tipoFacturaDocumento',
                    'codigoMotivo',
                    'cuf',
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct($this->wsdl, $this->availableFunctions);
        $this->sistema = AutorizacionSistema::where('estado', 'ACTIVO')->firstOrFail();
    }

    /**
     * Obtener valores de autorizacion de sistema
     *
     * @return AutorizacionSistema
     */
    public function getSistema() {
        return $this->sistema;
    }

    /**
     * Enviar factura para su recepcion.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return mixed
     */
    public function sendInvoice(array $params) {

        $response = $this->recepcionFactura([
            'SolicitudServicioRecepcionFactura' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],//0 para omitir
                'codigoDocumentoSector' => $params['sector_document_code'],//Tipo de documento facturaCompraVenta por defecto?
                'codigoEmision' => $params['emission_code'], //1 online, 2 offline, 3 masive
                'cuis' => $params['cuis'], //Cuis de la sucursal/punto de venta
                'cufd' => $params['cufd'], //cufd de la sucursal/punto de venta
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'archivo' => $params['file'],// alfanumerico?
                'fechaEnvio' => $params['send_date'],
                'hashArchivo' => $params['hash_file'],
            ]
        ]);

        return $response;
    }

    /**
     * Envia paquete de facturas para su recepcion.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return mixed
     */
    public function sendPackageInvoice(array $params) {

        $response = $this->recepcionPaqueteFactura([
            'SolicitudServicioRecepcionPaquete' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoDocumentoSector' => $params['sector_document_code'],
                'codigoEmision' => $params['emission_code'],
                'cuis' => $params['cuis'],
                'cufd' => $params['cufd'],
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'archivo' => $params['file'],
                'fechaEnvio' => $params['send_date'],
                'hashArchivo' => $params['hash_file'],
                'cafc' => $params['cafc'],// null
                'cantidadFacturas' => $params['number_invoices'],
                'codigoEvento' => $params['event_code'],// Codigo que devolvio al registrar el evento
            ]
        ]);

        return $response;
    }

    /**
     * Validar recepcion de paquete de facturas.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     *
     * @return mixed
     */
    public function validateReceptionPackageInvoice(array $params) {
        $response = $this->validacionRecepcionPaqueteFactura([
            'SolicitudServicioValidacionRecepcionPaquete' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoDocumentoSector' => $params['sector_document_code'],
                'codigoEmision' => $params['emission_code'],
                'cuis' => $params['cuis'],
                'cufd' => $params['cufd'],
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'codigoRecepcion' => $params['reception_code'],
            ]
        ]);

        return $response;
    }

    /**
     * Obtener errores devueltos por el servicio web de SIAT en cadena de texto.
     *
     * @param mixed $response
     *
     * @return string
     */
    public function getSiatErrorsString($response) {
        $errors = (array) $response->mensajesList;
        $errorsString = '';

        if( is_null($errors) ){
            return $errorsString;
        }

        if( isset($errors[0]) ) {
            foreach ($errors as $key => $error) {
                $errorsString .= sprintf("    [%s] %s", $error->codigo, $error->descripcion);
                $errorsString .= "\n";
            }
        } else {
            $errorsString .= sprintf("    [%s] %s", $errors['codigo'], $errors['descripcion']);
            $errorsString .= "\n";
        }

        return $errorsString;
    }

    /**
     * Anular factura recepcionada en SIAT.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return mixed
     */
    public function anulateInvoice(array $params) {
        $response = $this->anulacionFactura([
            'SolicitudServicioAnulacionFactura' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoDocumentoSector' => $params['sector_document_code'],
                'codigoEmision' => $params['emission_code'],
                'cuis' => $params['cuis'],
                'cufd' => $params['cufd'],
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'codigoMotivo' => $params['motive_code'],
                'cuf' => $params['cuf'],
            ]
        ]);

        return $response;
    }

    public function verifyInvoice(array $params)
    {
//        dd($params);
        $response = $this->verificacionEstadoFactura([
           'SolicitudServicioVerificacionEstadoFactura' => [
               'codigoAmbiente' => $this->sistema->codigo_ambiente,
               'codigoSistema' => $this->sistema->codigo_sistema,
               'codigoModalidad' => $this->sistema->codigo_modalidad,
               'nit' => $this->sistema->nit,
               'codigoSucursal' => $params['branch_code'],
               'codigoPuntoVenta' => $params['pos_code'],
               'codigoDocumentoSector' => $params['sector_document_code'],
               'codigoEmision' => $params['emission_code'],
               'cuis' => $params['cuis'],
               'cufd' => $params['cufd'],
               'tipoFacturaDocumento' => $params['invoice_type_code'],
               'cuf' => $params['cuf'],
           ]
        ]);
       return $response;
    }

    public function communicationVerification()
    {
        $response = $this->verificarComunicacion();
        return $response->return;
    }

    public function sendMassiveInvoice(array $params)
    {
        $response = $this->recepcionMasivaFactura([
            'SolicitudServicioRecepcionMasiva' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoDocumentoSector' => $params['sector_document_code'],
                'codigoEmision' => $params['emission_code'],
                'cuis' => $params['cuis'],
                'cufd' => $params['cufd'],
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'archivo' => $params['file'],
                'fechaEnvio' => $params['send_date'],
                'hashArchivo' => $params['hash_file'],
                'cantidadFacturas' => $params['number_invoices'],
            ]
        ]);
        return $response;
    }
    public function validateReceptionMassiveInvoice(array $params)
    {
        $response = $this->validacionRecepcionMasivaFactura([
            'SolicitudServicioValidacionRecepcionMasiva' => [
                'codigoAmbiente' => $this->sistema->codigo_ambiente,
                'codigoSistema' => $this->sistema->codigo_sistema,
                'codigoModalidad' => $this->sistema->codigo_modalidad,
                'nit' => $this->sistema->nit,
                'codigoSucursal' => $params['branch_code'],
                'codigoPuntoVenta' => $params['pos_code'],
                'codigoDocumentoSector' => $params['sector_document_code'],
                'codigoEmision' => $params['emission_code'],
                'cuis' => $params['cuis'],
                'cufd' => $params['cufd'],
                'tipoFacturaDocumento' => $params['invoice_type_code'],
                'codigoRecepcion' => $params['reception_code'],
            ]
        ]);
        return $response;
    }
}
