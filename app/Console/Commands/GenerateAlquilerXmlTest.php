<?php

namespace App\Console\Commands;

use App\Http\Traits\Siat\XmlFile;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateAlquilerXmlTest extends Command
{
    use XmlFile;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generate-alquiler-xml {--save}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Genera un XML de prueba para factura de alquiler de bien inmueble (Sector 2)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Generando XML de Alquiler de Bien Inmueble (Sector 2)...\n');

        // Datos inventados de la empresa QUE ALQUILA (Emisor)
        $datosEmisor = [
            'nitEmisor' => '1754238962',
            'razonSocialEmisor' => 'CONSTRUCTORA REAL ESTATE S.A.',
            'municipio' => 'La Paz',
            'telefono' => '2334567890',
            'codigoSucursal' => 0,
            'direccion' => 'Av. Arce 2850, Edificio Corporativo la Paz',
            'usuario' => 'cperez',
        ];

        // Datos inventados del CLIENTE que ALQUILA (Receptor)
        $datosCliente = [
            'nombreRazonSocial' => 'FERRETERIA CONSTRUCTORES UNIDOS',
            'codigoTipoDocumentoIdentidad' => 1,
            'numeroDocumento' => '3891235',
            'complemento' => null,
            'codigoCliente' => 'CLI-2024-001',
        ];

        // Datos inventados de la propiedad a alquilar
        $datosPropiedad = [
            'descripcion' => 'ALQUILER LOCAL COMERCIAL PISO 2 - ZONA CENTRAL',
            'actividadEconomica' => '681011', // Alquiler de inmuebles comerciales
            'codigoProductoSin' => '72111',   // Código SIN para alquiler
            'codigoProducto' => 'ALQ-LP-2024-001',
            'cantidadMeses' => 1,
            'precioMensual' => 2500, // Bs. 2500 por mes
            'cantidadDías' => 30,
        ];

        // Armar datos completos del XML
        $data = [
            'cabecera' => [
                'nitEmisor' => $datosEmisor['nitEmisor'],
                'razonSocialEmisor' => $datosEmisor['razonSocialEmisor'],
                'municipio' => $datosEmisor['municipio'],
                'telefono' => $datosEmisor['telefono'],
                'numeroFactura' => '001',
                'cuf' => '44AAEC00DBD34C819B4D7AFD5F91900D3A059E06A467A75AC82F24C74',
                'cufd' => 'BQUE+QytqQUDBKVUFOSVRPQkxVRFZNVFVJBMDAwMDAwM',
                'codigoSucursal' => $datosEmisor['codigoSucursal'],
                'direccion' => $datosEmisor['direccion'],
                'codigoPuntoVenta' => 1,
                'fechaEmision' => Carbon::now()->format('Y-m-d\TH:i:s.v'),
                'nombreRazonSocial' => $datosCliente['nombreRazonSocial'],
                'codigoTipoDocumentoIdentidad' => $datosCliente['codigoTipoDocumentoIdentidad'],
                'numeroDocumento' => $datosCliente['numeroDocumento'],
                'complemento' => $datosCliente['complemento'],
                'codigoCliente' => $datosCliente['codigoCliente'],
                'periodoFacturado' => 'Mayo 2026', // Período de alquiler
                'codigoMetodoPago' => 1, // Efectivo
                'numeroTarjeta' => null,
                'montoTotal' => $datosPropiedad['precioMensual'],
                'montoTotalSujetoIva' => $datosPropiedad['precioMensual'],
                'codigoMoneda' => 1, // Bolivianos
                'tipoCambio' => 1,
                'montoTotalMoneda' => $datosPropiedad['precioMensual'],
                'descuentoAdicional' => 0,
                'codigoExcepcion' => 0,
                'cafc' => null,
                'leyenda' => 'Ley N° 453: Tienes derecho a recibir información sobre plazos de entrega, precio, garantía y condiciones de la relación comercial antes de contratar.',
                'usuario' => $datosEmisor['usuario'],
                'codigoDocumentoSector' => 2, // ✅ Sector 2: Alquiler
            ],
            'detalle' => [
                [
                    'actividadEconomica' => $datosPropiedad['actividadEconomica'],
                    'codigoProductoSin' => $datosPropiedad['codigoProductoSin'],
                    'codigoProducto' => $datosPropiedad['codigoProducto'],
                    'descripcion' => $datosPropiedad['descripcion'],
                    'cantidad' => $datosPropiedad['cantidadMeses'],
                    'unidadMedida' => 67, // Mes (Código SIN para mes)
                    'precioUnitario' => $datosPropiedad['precioMensual'],
                    'montoDescuento' => 0,
                    'subTotal' => $datosPropiedad['precioMensual'],
                    // ✅ NO incluir numeroSerie ni numeroImei (Solo para Compra-Venta)
                ]
            ],
        ];

        // Generar el XML
        $xmlContent = $this->generateXmlAlquiler('electronica', $data, 'Mayo 2026');

        // Mostrar información en la consola
        $this->line("\n📋 <fg=green;options=bold>DATOS DEL EMISOR (Empresa que Alquila)</>\n");
        $this->table(['Campo', 'Valor'], [
            ['NIT', $datosEmisor['nitEmisor']],
            ['Razón Social', $datosEmisor['razonSocialEmisor']],
            ['Teléfono', $datosEmisor['telefono']],
            ['Municipio', $datosEmisor['municipio']],
            ['Dirección', $datosEmisor['direccion']],
        ]);

        $this->line("\n👤 <fg=green;options=bold>DATOS DEL CLIENTE (Que Alquila el Bien)</>\n");
        $this->table(['Campo', 'Valor'], [
            ['Razón Social', $datosCliente['nombreRazonSocial']],
            ['Tipo de Documento', 'CI'],
            ['Número de Documento', $datosCliente['numeroDocumento']],
            ['Código Cliente', $datosCliente['codigoCliente']],
        ]);

        $this->line("\n🏠 <fg=green;options=bold>DATOS DEL BIEN A ALQUILAR</>\n");
        $this->table(['Campo', 'Valor'], [
            ['Descripción', $datosPropiedad['descripcion']],
            ['Código Producto', $datosPropiedad['codigoProducto']],
            ['Actividad Económica', $datosPropiedad['actividadEconomica']],
            ['Precio Mensual', 'Bs. ' . number_format($datosPropiedad['precioMensual'], 2)],
            ['Período', 'Mayo 2026'],
        ]);

        $this->line("\n✅ <fg=green;options=bold>XML GENERADO (Primeros 2000 caracteres)</>\n");
        $this->line("<fg=cyan>" . substr($xmlContent, 0, 2000) . "...</>");

        // Si se especifica --save, guardar el XML
        if ($this->option('save')) {
            $fileName = 'alquiler_' . Carbon::now()->format('YmdHis') . '.xml';
            $path = storage_path('app/xml_test/' . $fileName);
            
            // Crear directorio si no existe
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            file_put_contents($path, $xmlContent);
            $this->info("\n💾 XML guardado en: <fg=yellow>storage/app/xml_test/{$fileName}</>");
        }

        $this->info("\n✨ <fg=green;options=bold>¡XML de Alquiler generado correctamente!</>\n");

        return 0;
    }
}
