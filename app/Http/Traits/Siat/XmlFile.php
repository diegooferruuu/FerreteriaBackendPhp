<?php

namespace App\Http\Traits\Siat;

use App\Http\Services\Siat\ArrayToXml;

/**
 * Generador de archivos XML
 */
trait XmlFile
{
    private $invoiceDataExample = [
        'cabecera' => [
            'nitEmisor' => '1003579028',
            'razonSocialEmisor' => 'Emapa',
            'municipio' => 'La Paz',
            'telefono' => '2846005',
            'numeroFactura' => '1',
            'cuf' => '44AAEC00DBD34C819B4D7AFD5F91900D3A059E06A467A75AC82F24C74',
            'cufd' => 'BQUE+QytqQUDBKVUFOSVRPQkxVRFZNVFVJBMDAwMDAwM',
            'codigoSucursal' => 0,
            'direccion' => 'Mi direccion',
            'codigoPuntoVenta' => null,
            'fechaEmision' => '2022-10-05T09:01:24.178',
            'nombreRazonSocial' => 'Mi cliente',
            'codigoTipoDocumentoIdentidad' => 1,
            'numeroDocumento' => '5115889',
            'complemento' => null,
            'codigoCliente' => '51158891',
            'codigoMetodoPago' => 1,
            'numeroTarjeta' => null,
            'montoTotal' => 99,
            'montoTotalSujetoIva' => 99,
            'codigoMoneda' => 1,
            'tipoCambio' => 1,
            'montoTotalMoneda' => 99,
            'montoGiftCard' => null,
            'descuentoAdicional' => 1,
            'codigoExcepcion' => null,
            'cafc' => null,
            'leyenda' => 'Ley N° 453: Tienes derecho a recibir información sobre las características y contenidos de los servicios que utilices.',
            'usuario' => 'pperez',
            'codigoDocumentoSector' => 1,
        ],
        'detalle' => [
            [
                'actividadEconomica' => '451010',
                'codigoProductoSin' => '49111',
                'codigoProducto' => 'JN-131231',
                'descripcion' => 'JUGO DE NARANJA EN VASO',
                'cantidad' => '1',
                'unidadMedida' => '1',
                'precioUnitario' => '100',
                'montoDescuento' => '0',
                'subTotal' => '100',
                'numeroSerie' => '124548',
                'numeroImei' => '545454',
            ]
        ],
    ];

    /**
     * Generar XML de factura electrónica o computarizada
     * 
     * @param $type     Tipo de factura electrónica o computarizada
     * @param $data     Array de datos que contienen información de la factura a generar
     * @return string   XML
     */
    public function generateXmlInvoice(string $type = 'electronica', array $data = [])
    {
        if( !in_array($type, ['electronica', 'computarizada']) ) {
            throw new \Exception("Error Processing Request");
        }

        $type = ucfirst($type);
        $data = empty($data) ? $this->invoiceDataExample : $data;

        return ArrayToXml::convert($data, [
            'rootElementName' => "factura{$type}CompraVenta",
            '_attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:noNamespaceSchemaLocation' => "factura{$type}CompraVenta.xsd", //Location xsd
            ],
        ], true, 'UTF-8', '1.0', [], true);
    }

    /**
     * Generar XML de factura de alquiler de bien inmueble
     * 
     * @param string $type      Tipo de factura: 'electronica' o 'computarizada'
     * @param array $data       Array de datos que contienen información de la factura
     * @param string $periodo   Período facturado (ej: "01/05/2026 - 31/05/2026")
     * @return string           XML de alquiler
     */
    public function generateXmlAlquiler(string $type = 'electronica', array $data = [], string $periodo = '')
    {
        if( !in_array($type, ['electronica', 'computarizada']) ) {
            throw new \Exception("Error Processing Request");
        }

        $type = ucfirst($type);
        
        // Si no hay período, generar automáticamente (del 1 al último día del mes actual)
        if(empty($periodo)) {
            $hoy = \Carbon\Carbon::now();
            $primerDia = $hoy->copy()->firstOfMonth()->format('d/m/Y');
            $ultimoDia = $hoy->copy()->lastOfMonth()->format('d/m/Y');
            $periodo = "{$primerDia} - {$ultimoDia}";
        }

        // Agregar período a los datos si no existe
        if(!isset($data['cabecera']['periodoFacturado'])) {
            $data['cabecera']['periodoFacturado'] = $periodo;
        }

        // Establecer codigoDocumentoSector = 2 para alquileres
        $data['cabecera']['codigoDocumentoSector'] = 2;

        return ArrayToXml::convert($data, [
            'rootElementName' => "factura{$type}AlquilerBienInmueble",
            '_attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                'xsi:noNamespaceSchemaLocation' => "factura{$type}AlquilerBienInmueble.xsd",
            ],
        ], true, 'UTF-8', '1.0', [], true);
    }
}
