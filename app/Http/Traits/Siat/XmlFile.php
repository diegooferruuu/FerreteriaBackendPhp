<?php

namespace App\Http\Traits\Siat;

use App\Http\Services\Siat\ArrayToXml;
use Carbon\Carbon;

/**
 * Generador de archivos XML para múltiples sectores del SIAT
 */
trait XmlFile
{
    // Plantilla de prueba para Compra-Venta (Sector 1)
    private $exampleCompraVenta = [
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
            'fechaEmision' => '2026-05-16T09:01:24.178',
            'nombreRazonSocial' => 'Mi cliente',
            'codigoTipoDocumentoIdentidad' => 1,
            'numeroDocumento' => '5115889',
            'complemento' => null,
            'codigoCliente' => '51158891',
            'codigoMetodoPago' => 1,
            'numeroTarjeta' => null,
            'montoTotal' => 100,
            'montoTotalSujetoIva' => 100,
            'codigoMoneda' => 1,
            'tipoCambio' => 1,
            'montoTotalMoneda' => 100,
            'montoGiftCard' => 0, // Válido en Compra-Venta
            'descuentoAdicional' => 0,
            'codigoExcepcion' => null,
            'cafc' => null,
            'leyenda' => 'Ley N° 453: Tienes derecho a recibir información...',
            'usuario' => 'pperez',
            'codigoDocumentoSector' => 1, 
        ],
        'detalle' => [
            [
                'actividadEconomica' => '451010',
                'codigoProductoSin' => '49111',
                'codigoProducto' => 'JN-131231',
                'descripcion' => 'PRODUCTO DE PRUEBA',
                'cantidad' => '1',
                'unidadMedida' => '1',
                'precioUnitario' => '100',
                'montoDescuento' => '0',
                'subTotal' => '100',
                'numeroSerie' => '124548', // Válido en Compra-Venta
                'numeroImei' => '545454',  // Válido en Compra-Venta
            ]
        ],
    ];

    // Plantilla de prueba para Alquiler de Bien Inmueble (Sector 2) [cite: 82]
    // ORDEN CRÍTICO: Debe coincidir exactamente con el XSD
    private $exampleAlquiler = [
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
            'fechaEmision' => '2026-05-16T09:01:24.178',
            'nombreRazonSocial' => 'Mi cliente',
            'codigoTipoDocumentoIdentidad' => 1,
            'numeroDocumento' => '5115889',
            'complemento' => null,
            'codigoCliente' => '51158891',
            'periodoFacturado' => 'Mayo 2026', // Obligatorio para Alquiler [cite: 43] - DEBE ir ANTES de codigoMetodoPago
            'codigoMetodoPago' => 1,
            'numeroTarjeta' => null,
            'montoTotal' => 500,
            'montoTotalSujetoIva' => 500,
            'codigoMoneda' => 1,
            'tipoCambio' => 1,
            'montoTotalMoneda' => 500,
            'descuentoAdicional' => 0,
            'codigoExcepcion' => null,
            'cafc' => null,
            'leyenda' => 'Ley N° 453: Tienes derecho a recibir información...',
            'usuario' => 'pperez',
            'codigoDocumentoSector' => 2, // Fijo para Alquiler [cite: 82]
        ],
        'detalle' => [
            [
                'actividadEconomica' => '681011',
                'codigoProductoSin' => '72111',
                'codigoProducto' => 'ALQ-001',
                'descripcion' => 'ALQUILER MENSUAL LOCAL COMERCIAL',
                'cantidad' => '1',
                'unidadMedida' => '1',
                'precioUnitario' => '500',
                'montoDescuento' => '0',
                'subTotal' => '500',
                // Sin IMEI ni Número de Serie (No existen en el XSD de Alquiler)
            ]
        ],
    ];

    /**
     * Generar XML de Factura Compra-Venta (Sector 1)
     */
    public function generateXmlInvoice(string $type = 'electronica', array $data = [])
    {
        $this->validateInvoiceType($type);
        $type = ucfirst($type);
        
        // Si no hay datos, usa el ejemplo de Compra-Venta
        $data = empty($data) ? $this->exampleCompraVenta : $data;

        // Forzar sector correcto
        $data['cabecera']['codigoDocumentoSector'] = 1;

        return ArrayToXml::convert($data, [
            'rootElementName' => "factura{$type}CompraVenta",
            '_attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:noNamespaceSchemaLocation' => "factura{$type}CompraVenta.xsd",
            ],
        ], true, 'UTF-8', '1.0', [], true);
    }

    /**
     * Generar XML de Factura de Alquiler de Bien Inmueble (Sector 2) [cite: 82]
     */
    public function generateXmlAlquiler(string $type = 'electronica', array $data = [], string $periodo = '')
    {
        $this->validateInvoiceType($type);
        $type = ucfirst($type);
        
        // Si no hay datos, usa el ejemplo de Alquiler
        if (empty($data)) {
            $data = $this->exampleAlquiler;
        }

        // Manejo automático del periodoFacturado si viene vacío [cite: 43]
        if (empty($periodo) && !isset($data['cabecera']['periodoFacturado'])) {
            $hoy = Carbon::now();
            $data['cabecera']['periodoFacturado'] = $hoy->copy()->firstOfMonth()->format('d/m/Y') . " al " . $hoy->copy()->lastOfMonth()->format('d/m/Y');
        } elseif (!empty($periodo)) {
            $data['cabecera']['periodoFacturado'] = $periodo;
        }

        // Forzar sector correcto [cite: 82]
        $data['cabecera']['codigoDocumentoSector'] = 2;

        // REORDENAR cabecera según XSD (después de agregar periodoFacturado)
        $cabeceraOrdenada = [];
        $orden = ['nitEmisor', 'razonSocialEmisor', 'municipio', 'telefono', 'numeroFactura', 'cuf', 'cufd', 'codigoSucursal', 'direccion', 'codigoPuntoVenta', 'fechaEmision', 'nombreRazonSocial', 'codigoTipoDocumentoIdentidad', 'numeroDocumento', 'complemento', 'codigoCliente', 'periodoFacturado', 'codigoMetodoPago', 'numeroTarjeta', 'montoTotal', 'montoTotalSujetoIva', 'codigoMoneda', 'tipoCambio', 'montoTotalMoneda', 'descuentoAdicional', 'codigoExcepcion', 'cafc', 'leyenda', 'usuario', 'codigoDocumentoSector'];
        
        foreach ($orden as $key) {
            if (isset($data['cabecera'][$key])) {
                $cabeceraOrdenada[$key] = $data['cabecera'][$key];
            }
        }
        $data['cabecera'] = $cabeceraOrdenada;

        // SANITIZACIÓN: Elimina datos de Compra-Venta si se colaron en Alquiler
        unset($data['cabecera']['montoGiftCard']);
        if (isset($data['detalle']) && is_array($data['detalle'])) {
            foreach ($data['detalle'] as &$item) {
                unset($item['numeroSerie']);
                unset($item['numeroImei']);
            }
        }

        return ArrayToXml::convert($data, [
            'rootElementName' => "factura{$type}AlquilerBienInmueble",
            '_attributes' => [
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:noNamespaceSchemaLocation' => "factura{$type}AlquilerBienInmueble.xsd",
            ],
        ], true, 'UTF-8', '1.0', [], true);
    }

    /**
     * Validador privado de tipos de factura
     */
    private function validateInvoiceType(string $type)
    {
        if (!in_array($type, ['electronica', 'computarizada'])) {
            throw new \Exception("Tipo de emisión SIAT no soportado: {$type}");
        }
    }
}