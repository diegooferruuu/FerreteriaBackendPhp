<?php

namespace App\Http\Services;

use App\Enums\EmissionCode;
use App\Enums\ReceptionType;
use App\Http\Helpers\XMLSecLibs\SignedXml;
use App\Http\Services\Siat\SalePurchaseInvoice;
use App\Http\Services\Siat\XmlValidator;
use App\Http\Traits\EstadoSistema;
use App\Http\Traits\Siat\CompressFile;
use App\Http\Traits\Siat\Cuf;
use App\Http\Traits\Siat\Hasher;
use App\Http\Traits\Siat\XmlFile;
use App\Models\AnulacionFactura;
use App\Models\Cufd;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\Recepcion;
use App\Models\SincronizacionCatalogo;
use App\Models\ValorCatalogo;
use App\Models\Venta;
use Carbon\Carbon;
use DateTime;
use http\Message;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use function GuzzleHttp\Promise\queue;

/**
 * Gestion de facturas
 */
class FacturaService
{
    use Cuf, XmlFile, CompressFile, Hasher, EstadoSistema;

    /**
     * instancia de servicio de compra y venta
     *
     * @var SalePurchaseInvoice
     */
    protected $serviceCompraVenta;

    /**
     * Datos de autorizacion de sistema
     *
     * @var AutorizacionSistema
     */
    protected $sistema;

    /**
     * Codigo que identifica el sector de la factura
     *
     * @var int
     */
    protected $codigoDocumentoSector;

    /**
     * Codigo que identifica el tipo de factura o documento
     *
     * @var int
     */
    protected $tipoFactura;

    /**
     * Codigo que identifica si la emision fue online, offline
     *
     * @var int
     */
    protected $codigoEmision;

    /*
     * Estado sistema global ONLINE o OFFLNE
     */
    protected $estadoSistema;

    public function __construct()
    {
        $this->serviceCompraVenta = new SalePurchaseInvoice();
        $this->sistema = $this->serviceCompraVenta->getSistema();
        $this->codigoDocumentoSector = 1;
        $this->tipoFactura = 1;
        $this->codigoEmision = EmissionCode::ONLINE->value;
//        dd("hola");
//        $this->estadoSistema = $this->getEstadoSistema();
    }

    /**
     * Registrar factura localmente y emitir o recepcionar la factura en SIAT en modalidad online
     *
     * @param Venta $venta
     * @return void
     */
    public function register(Venta $venta)
    {

        $venta->load([
            'pos',
            'inventarios.producto' => ['homologacion.catalogoProducto', 'atributos']
        ]);

        $params = [];

        $params['branchId'] = null;
        $params['posId'] = $venta->pos->id;

        if (!$venta->pos->cuis) {
            throw new \Exception("Establece un CUIS para el punto de venta");
        }

        $params['cuis'] = $venta->pos->cuis->valor;

        if (!$venta->pos->cuis->cufd) {
            throw new \Exception("Establece un CUFD para el punto de venta");
        }

        //ver si hay evento establecer cufd evento
        if ($venta->pos->is_offline || $venta->sucursal->is_offline) {
            $this->codigoEmision = EmissionCode::OFFLINE->value;
            $params['eventoSignificativoId'] = $venta->pos?->eventoSignificativo->id ?? $venta->sucursal?->eventoSignificativo->id;
            $valorCufdEvento = $venta->pos?->eventoSignificativo->cufd_evento;
            $params['cufd'] = $valorCufdEvento;
            $cufd =  Cufd::where('valor',$valorCufdEvento)->first();
            $params['cufdId'] =$cufd->id;
            $params['codigoControl'] = $cufd->codigo_control;
        }else {
            //si es normal
            $params['cufd'] = $venta->pos->cuis->cufd->valor;
            $params['cufdId'] = $venta->pos->cuis->cufd->id;
            $params['codigoControl'] = $venta->pos->cuis->cufd->codigo_control;
        }

        $params['fecha'] = $venta->fecha;
        $params['branchCode'] = $venta->sucursal->codigo_siat;
        $params['posCode'] = $venta->pos->codigo_siat;
        $params['departamento'] = $venta->sucursal->departamento->departamento;
        $params['telefono'] = $venta->sucursal->telefono;
        $params['direccion'] = $venta->sucursal->direccion;




        $factura = $this->createInvoice($venta, $params);

        if($factura === false)
        {
            throw new \Exception("No se encontro archivos para la firma de documentos");
        }

        // Emision online
        if (EmissionCode::from($this->codigoEmision) == EmissionCode::ONLINE) {
                $this->emitOnline($factura, $params);
        }


//        if($codeResult === 0 ) {
        // Emision offline
        if (EmissionCode::from($this->codigoEmision) == EmissionCode::OFFLINE) {
            $this->emitOffline($factura, $params['eventoSignificativoId']);
        }

//        }
        return $factura;
    }

    /**
     * Crear factura generando su xml firmado
     *
     * @param Venta $venta
     * @param array $params Argumentos necesarios para generar la factura
     * @return Factura
     */
    public function createInvoice(Venta $venta, $params,$eventoSignificativo = null, $emisionMasiva = null)
    {
        $dataCatalogoLeyenda = SincronizacionCatalogo::where('syncable_type','sucursal')->where('syncable_id',1)->where('catalogo_facturacion_id',3)->get()[0]->valores;
//        dd($dataCatalogoLeyenda);
        $leyenda = $dataCatalogoLeyenda->random()->descripcion; // 'Ley N° 453: Está prohibido importar, distribuir o comercializar productos expirados o prontos a expirar.';

        $usuario = auth()->user()?->username ?? 'pperez';
        $codigoMoneda = 1;
        $tipoCambio = 1;
        if($venta->cliente->tipoDocumento->codigo_clasificador == '5')
        {
            $codigoExcepcion = 1;
        }else{
            $codigoExcepcion = 0;
        }


        $fecha = new DateTime($params['fecha']);

        //para evento significativo 5.6.7
        if(in_array($eventoSignificativo?->evento->codigo_clasificador,[5,6,7]))
        {
//            $params['cufd'] = $eventoSignificativo->cufd_evento;
            $params['cafc'] =   $eventoSignificativo->cafc;
            $this->codigoEmision = EmissionCode::OFFLINE->value;
        }else{
            $params['cafc'] =   ['_attributes' => ['xsi:nil' => "true"]];
        }
        if(!is_null($emisionMasiva))
        {
            $this->codigoEmision = EmissionCode::MASSIVE->value;
        }

//        dd($this->codigoEmision);
        // Generar CUF
        $cuf = $this->generateCuf(
            $this->sistema->nit,
            $fecha->format("YmdHisv"),
            $params['branchCode'],
            $this->sistema->codigo_modalidad,
            $this->codigoEmision,
            $this->tipoFactura,
            $this->codigoDocumentoSector,
            $venta->codigo_secuencia,
            $params['posCode']);
        $cuf = $cuf . $params['codigoControl'];

        //        dd($venta->cliente->tipo_documento_id);
        // array factura para xml
        $factura = [
            'cabecera' => [
                'nitEmisor' => $this->sistema->nit,//Emapa
                'razonSocialEmisor' => $this->sistema->razon_social,
                'municipio' => $params['departamento'],
                'telefono' => $params['telefono'],
                'numeroFactura' => $venta->codigo_secuencia,//Generar automaticamente por sucursal
                'cuf' => $cuf, //Concatenar el Codigo de control obtenida al solicitar CUFD
                'cufd' => $params['cufd'], //Cufd de la sucursal/punto de venta actual
                'codigoSucursal' => $params['branchCode'],
                'direccion' => $params['direccion'],
                'codigoPuntoVenta' => $params['posCode'],
                'fechaEmision' => $fecha->format('Y-m-d\TH:i:s.v'),
                'nombreRazonSocial' => $venta->cliente->razon_social,
                'codigoTipoDocumentoIdentidad' => $venta->cliente->tipoDocumento->codigo_clasificador, //Obtener codigo desde catalogos $venta->cliente->tipo_documento_id,
                'numeroDocumento' => $venta->cliente->cedula_nit,
                'complemento' => $venta->cliente->complemento ?? [
                        '_attributes' => ['xsi:nil' => "true"],
                    ],
                'codigoCliente' => $venta->cliente->id, //Generado por emapa
                'codigoMetodoPago' => $venta->metodoPago->codigo_metodo_pago_siat, //Codigo siat metodo de pago
                'numeroTarjeta' => obfuscate($venta->informacion_tarjeta, '0', 4, 4) ?? [
                        '_attributes' => ['xsi:nil' => "true"],
                    ],
                'montoTotal' => $venta->total,
                'montoTotalSujetoIva' => $venta->total,
                'codigoMoneda' => $codigoMoneda,//Codigo siat tipo moneda
                'tipoCambio' => $tipoCambio,
                'montoTotalMoneda' => $venta->total,
                'montoGiftCard' => [
                    '_attributes' => ['xsi:nil' => "true"],
                ],
                'descuentoAdicional' => $venta->descuento ?? 0,
                'codigoExcepcion' => $codigoExcepcion, // No validar nit de cliente
                'cafc' => $params['cafc'],
                'leyenda' => $leyenda,
                'usuario' => $usuario,
                'codigoDocumentoSector' => $this->codigoDocumentoSector,
            ],
        ];
        $detalle = [];

        // mappear detalle y validar que cada item se encuentre homologado
        foreach ($venta->inventarios as $item) {
            if (!$item->producto->homologacion) {
                throw new \Exception("{$item->producto->descripcion} no homologado");
                break;
            }

//            dd( );
            $detalle[] = [
                'actividadEconomica' => $item->producto->homologacion->catalogoProducto->codigo_actividad,
                'codigoProductoSin' => $item->producto->homologacion->codigo_siat,
                'codigoProducto' => $item->producto->id,
                'descripcion' => $item->producto->descripcion,
                'cantidad' => $item->pivot->cantidad,
                'unidadMedida' => (int)$item->producto->unidadMedida->valorCatalogo->codigo_clasificador,
                'precioUnitario' => $item->pivot->precio,
                'montoDescuento' => $item->pivot->descuento,
                'subTotal' => $item->pivot->sub_total,
                'numeroSerie' => null,
                'numeroImei' => null
            ];
        }

        $factura['detalle'] = $detalle;

        // Proceso SIAT
        //1.Generar archivo xml
        $xmlVenta = $this->generateXmlInvoice('electronica', $factura);
        // return $xmlVenta;
        $randomNameXml = uniqid('f_', true);
        //nombre path a subir
        //modo offline

        if (EmissionCode::from($this->codigoEmision) == EmissionCode::OFFLINE || in_array($eventoSignificativo?->evento->codigo_clasificador,[5,6,7])) {
            $xmlFilePath = "facturas/" . $venta->punto_venta_id . "/" . $fecha->format('d-m-Y') . "/paquete/xml/" . $randomNameXml . ".xml";
        } elseif(!is_null($emisionMasiva))
        {
            $xmlFilePath = "facturas/" . $venta->punto_venta_id . "/" . $fecha->format('d-m-Y') . "/masivo/xml/" . $randomNameXml . ".xml";
        } else {
            //modo online
            $xmlFilePath = "facturas/" . $venta->punto_venta_id . "/" . $fecha->format('d-m-Y') . "/xml/" . $randomNameXml . ".xml";
        }

        //2. Firmar el archivo
        try {
//            dd($xmlFilePath);
            // $certPath = public_path('emapa.pfx.pem'); // Antes convertir pfx -> pem (private+certificate keys)

            $pfxContent = (new FirmaService())->getPfxContent();

            if($pfxContent === false )
            {
                return false;
            }
            $signer = new SignedXml();

            $signer->setCertificate($pfxContent);

            $xmlSigned = $signer->signXml($xmlVenta);

           Storage::disk('private')->put($xmlFilePath, $xmlSigned);

        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        //3. Validar contra el XSD
        try {
            $xmlSignedPath = Storage::disk('private')->path($xmlFilePath);

            $xmlValidate = new XmlValidator();
            $xmlValidated = $xmlValidate->validate($xmlSignedPath, public_path('facturaElectronicaCompraVenta.xsd'));
            if (!$xmlValidated) {
                throw new \Exception("{$xmlValidate->getXmlErrorsString()}");
            }
        } catch (\Throwable $th) {
            throw $th;
        }
//        dd( json_decode($venta));
        // Registro de factura local
        $nuevaFactura = Factura::create([
            'codigo_documento_sector' => $this->codigoDocumentoSector,
            'codigo_tipo_factura' => $this->tipoFactura,
            'numero_documento_identidad' => $venta->cliente->cedula_nit,
            'codigo_documento_identidad' => $venta->cliente->tipo_documento_id, //$venta->cliente->tipoDocumento->codigo_clasificador,
            'codigo_metodo_pago' => $venta->metodoPago->codigo_metodo_pago_siat,
            'codigo_cliente' => $venta->cliente->id,
            'razon_social' => $venta->cliente->razon_social,
            'leyenda' => $leyenda,
            'usuario' => $usuario,
            'cuf' => $cuf,
            'cafc' => null,
            'xml' => $xmlFilePath,
            'venta_id' => $venta->id,
            'cufd_id' => $params['cufdId'],
        ]);

        return $nuevaFactura;
    }

    /**
     * Emision online de facturas
     *
     * @param Factura $factura
     * @param array $params
     * @return void
     */
    public function emitOnline(Factura $factura, $params)
    {

        $fecha = new DateTime($params['fecha']);

        //4. Comprimir xml en gzip
        try {
            $xmlSignedPath = Storage::disk('private')->path($factura->xml);
            $compressFilePath = $this->gzipCompressFile($xmlSignedPath);
        } catch (\Throwable $th) {
            throw $th;
        }

        //5. Obtener Hash del gzip
        $hash256Compress = $this->hash256($compressFilePath);

        //6. Envio por servicio web "RecepcionFactura"
        try {
            $handle = fopen($compressFilePath, "r");
            $contents = fread($handle, filesize($compressFilePath));
            fclose($handle);

            $responseRecepcionFactura = $this->serviceCompraVenta->recepcionFactura([
                'SolicitudServicioRecepcionFactura' => [
                    'codigoAmbiente' => $this->sistema->codigo_ambiente,
                    'codigoSistema' => $this->sistema->codigo_sistema,
                    'codigoModalidad' => $this->sistema->codigo_modalidad,
                    'nit' => $this->sistema->nit,
                    'codigoPuntoVenta' => $params['posCode'],
                    'codigoSucursal' => $params['branchCode'],
                    'codigoDocumentoSector' => $this->codigoDocumentoSector,
                    'codigoEmision' => $this->codigoEmision,
                    'cufd' => $params['cufd'], //cufd de la sucursal/punto de venta
                    'cuis' => $params['cuis'], //Cuis de la sucursal/punto de venta
                    'tipoFacturaDocumento' => $this->tipoFactura,
                    'archivo' => $contents,// alfanumerico?
                    'fechaEnvio' => $fecha->format('Y-m-d\TH:i:s.v'),
                    'hashArchivo' => $hash256Compress
                ]
            ]);
        } catch (\Throwable $error) {

            throw new \Exception("{$error->getMessage()}", $error->getCode());
        }

        if (!$responseRecepcionFactura->RespuestaServicioFacturacion->transaccion) {
            $errorsString = $this->serviceCompraVenta->getSiatErrorsString($responseRecepcionFactura->RespuestaServicioFacturacion);
//            dd( $errorsString);
            throw new \Exception("$errorsString");
        }
        // end

        // Registro recepcion
        $recepcion = Recepcion::create([
            'tipo' => ReceptionType::INDIVIDUAL,
            'codigo_emision' => $this->codigoEmision,
            'fecha_envio' => $fecha->format('Y-m-d\TH:i:s.v'),
            'hash_archivo' => $hash256Compress,
            'cantidad_facturas' => 1,
            'codigo_recepcion' => $responseRecepcionFactura->RespuestaServicioFacturacion->codigoRecepcion,
            'codigo_descripcion' => $responseRecepcionFactura->RespuestaServicioFacturacion->codigoDescripcion,
            'codigo_estado' => $responseRecepcionFactura->RespuestaServicioFacturacion->codigoEstado,
            'codigo_documento_sector' => $this->codigoDocumentoSector,
            'codigo_documento_fiscal' => $this->tipoFactura,
            'sucursal_id' => $params['branchId'],
            'punto_venta_id' => $params['posId'],
        ]);


        $factura->recepciones()->attach($recepcion->id, ['codigo_estado' => $responseRecepcionFactura->RespuestaServicioFacturacion->codigoEstado]);
        $factura->estado = 'RECEPCIONADO';
        $factura->save();
//        return $factura;
    }

    /**
     * Emision offline de facturas
     *
     * En caso de que estemos en un evento con codigo: 1,2,3 o 4 se debe generar facturas en la modalidad offline.
     *
     * @param Factura $factura
     * @param mixed $eventoSignificativoId
     * @return void
     */
    public function emitOffline(Factura $factura, $eventoSignificativoId)
    {
        $factura->eventosSignificativos()->attach($eventoSignificativoId);
    }
    public function emitMasive(Factura $factura, $emisionMasivaId)
    {
        $factura->emisionesMasivas()->attach($emisionMasivaId);
    }
    /**
     * Anular factura
     *
     * Realizamos la anulacion de la factura localmente y tambien del lado de SIAT.
     *
     * @param array $params Argumentos necesarios para llamar a la funcion.
     * @return AnulacionFactura
     */
    public function anulateInvoice($params)
    {
        $codigoEmision = 1;
        $factura = Factura::findOrFail($params['factura_id']);
        $codigoMotivo = ValorCatalogo::findOrFail($params['motivo_id'])?->codigo_clasificador;

        $pos = $factura->venta->pos;

        $branchCode = $pos->sucursal->codigo_siat;
        $posCode = $pos->codigo_siat;
        $codigoDocumentoSector = $factura->codigo_documento_sector;
        $cuis = $pos->cuis->valor;
        $cufd = $pos->cuis->cufd->valor;
        $codigoTipoFactura = $factura->codigo_tipo_factura;
        $cuf = $factura->cuf;

        $response = $this->serviceCompraVenta->anulateInvoice([
            'branch_code' => $branchCode,
            'pos_code' => $posCode,
            'sector_document_code' => $codigoDocumentoSector,
            'emission_code' => $codigoEmision,
            'cuis' => $cuis,
            'cufd' => $cufd,
            'invoice_type_code' => $codigoTipoFactura,
            'motive_code' => $codigoMotivo,
            'cuf' => $cuf,
        ]);

        if (!$response->RespuestaServicioFacturacion->transaccion) {
            $errorsString = $this->serviceCompraVenta->getSiatErrorsString($response->RespuestaServicioFacturacion);
            throw new \Exception("$errorsString");
        }

        $params['codigo_estado'] = $response->RespuestaServicioFacturacion->codigoEstado;
        $params['codigo_descripcion'] = $response->RespuestaServicioFacturacion->codigoDescripcion;

        $anulacionFactura = AnulacionFactura::create($params);

        $factura->update(['estado' => 'ANULADO']);
        $factura->venta->update(['estado' => 'ANULADO']);

        return $anulacionFactura;
    }

    public function receptionElectronicInvoicePackage($eventoSignificativo)
    {

        //codigo emision offline
        $codigoEmision = EmissionCode::OFFLINE->value;
        //compra venta
        $codigoDocumentoSector = 1;
        $codigoTipoFactura = 1;
//            $params['cantidadFacturas'] = $eventoSignificativo->facturas->count();

        if ($eventoSignificativo->facturas->count() == 0) {
            throw new \Exception("Ninguna factura emitida durante el evento significativo.");
        }
        if ($eventoSignificativo->sucursal_id && $eventoSignificativo->punto_venta_id ) {
            $cuis = $eventoSignificativo->sucursal->cuis->valor;
            $cufd = $eventoSignificativo->sucursal->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->sucursal->codigo_siat;
            $posCode = 0;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = $eventoSignificativo->sucursal->id;
            $posId = null;
            $cafc = $eventoSignificativo->cafc;
        }

        if ($eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->pos->cuis->valor;
            $cufd = $eventoSignificativo->pos->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->pos->sucursal->codigo_siat;
            $posCode = $eventoSignificativo->pos->codigo_siat;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = null;
            $posId = $eventoSignificativo->pos->id;
            $cafc = $eventoSignificativo->cafc;
        }


        // 1. Recuperar las facturas almacenadas en formato xml durante la etapa anterior
        $facturas = $eventoSignificativo->facturas->toArray();

        // 2. Formar paquetes de hasta 500 facturas
        $loteFacturas = array_chunk($facturas, 500);

//        $test = Storage::disk('private')->makeDirectory('facturas/paquetes');
        $packages = [];
        foreach ($loteFacturas as $lote) {
            $cantidadFacturas = count($lote);
            $facturaIds = [];
            $xmlFiles = [];
            $randomNameTar = uniqid('t_', true) . '.tar';
            //porciones de path
            $pathPorciones = explode('/', $lote[0]['xml']);

            $tarFilePath = Storage::disk('private')->path('facturas/'.$pathPorciones[1].'/'.$pathPorciones[2].'/'.$pathPorciones[3].'/'.$pathPorciones[4]) . '/' . $randomNameTar;
            foreach ($lote as $key => $factura) {

                $facturaIds[$factura['id']]['nro_archivo'] = $key;

                $xmlFiles[] = [
                    'path' => Storage::disk('private')->path($factura['xml']),
                    'name' => last(explode('/', $factura['xml']))
                ];

            }

            $this->tarPackFile($tarFilePath, $xmlFiles);

            try {
                $compressFilePath = $this->gzipCompressFile($tarFilePath);
            } catch (\Throwable $error) {
                throw new \Exception("{$error->getMessage()}", $error->getCode());
            }

            $hash256Compress = $this->hash256($compressFilePath);

            $packages[] = [
                'numberInvoices' => $cantidadFacturas,
                'invoiceIds' => $facturaIds,
                'tarPath' => $tarFilePath,
                'gzPath' => $compressFilePath,
                'gzHash' => $hash256Compress,
            ];

        }
        $responses = [];
        // 5.3. Enviar los paquetes de facturas
        try {
            $serviceCompraVenta = new SalePurchaseInvoice();
            foreach ($packages as $package) {

                $fecha = new DateTime();

                $handle = fopen($package['gzPath'], "r");
                $contents = fread($handle, filesize($package['gzPath']));

                fclose($handle);

                $response = $serviceCompraVenta->sendPackageInvoice([
                    'branch_code' => $branchCode,
                    'pos_code' => $posCode,
                    'sector_document_code' => $codigoDocumentoSector,
                    'emission_code' => $codigoEmision,
                    'cuis' => $cuis,
                    'cufd' => $cufd,
                    'invoice_type_code' => $codigoTipoFactura,
                    'file' => $contents,
                    'send_date' => $fecha->format('Y-m-d\TH:i:s.v'),
                    'hash_file' => $package['gzHash'],
                    'cafc' => $cafc,
                    'number_invoices' => $package['numberInvoices'],
                    'event_code' => $eventoSignificativo->codigo_recepcion,
                ]);

                if( !$response->RespuestaServicioFacturacion->transaccion ) {
                    $errorsString = $this->serviceCompraVenta->getSiatErrorsString($response->RespuestaServicioFacturacion);

                    throw new \Exception("$errorsString");
                }

                // Registro recepcion
                $recepcion = Recepcion::create([
                    'tipo' => ReceptionType::CONTINGENCY,
                    'codigo_emision' => $codigoEmision,
                    'fecha_envio' => $fecha->format('Y-m-d\TH:i:s.v'),
                    'archivo'=>'test',
                    'hash_archivo' => $package['gzHash'],
                    'cantidad_facturas' => $package['numberInvoices'],
                    'codigo_recepcion' => $response->RespuestaServicioFacturacion->codigoRecepcion,
                    'codigo_descripcion' => $response->RespuestaServicioFacturacion->codigoDescripcion,
                    'codigo_estado' => $response->RespuestaServicioFacturacion->codigoEstado,
                    'codigo_documento_sector' => $codigoDocumentoSector,
                    'codigo_documento_fiscal' => $codigoTipoFactura,
                    'sucursal_id' => $branchId,
                    'punto_venta_id' => $posId,
                    'evento_significativo_id' => $eventoSignificativo->id,
                ]);
                $eventoSignificativo->update([
                    'estado' => 'RECEPCIONADO',
                ]);
                $recepcion->facturas()->attach($package['invoiceIds']);
                $responses[] = $recepcion;
            }
        }catch (\Throwable $error) {

            throw new \Exception("{$error->getMessage()}", $error->getCode());
        }
        return $responses;
    }

    public function validate(EventoSignificativo $eventoSignificativo)
    {
        $codigoEmision = 2;

        if($eventoSignificativo->sucursal_id && $eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->sucursal->cuis->valor;
            $cufd = $eventoSignificativo->sucursal->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->sucursal->codigo_siat;
            $posCode = 0;
        }

        if($eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->pos->cuis->valor;
            $cufd = $eventoSignificativo->pos->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->pos->sucursal->codigo_siat;
            $posCode = $eventoSignificativo->pos->codigo_siat;
        }

        $serviceCompraVenta = new SalePurchaseInvoice();

        $responses = [];
        try {

        foreach ($eventoSignificativo->recepciones->where('evento_significativo_id',$eventoSignificativo->id) as $recepcion) {

            if( $recepcion->codigo_estado == 908 ) {
                continue;
            }

            $response = $serviceCompraVenta->validateReceptionPackageInvoice([
                'branch_code' => $branchCode,
                    'pos_code' => $posCode,
                    'sector_document_code' => $recepcion->codigo_documento_sector,
                    'emission_code' => $codigoEmision,
                    'cuis' => $cuis,
                    'cufd' => $cufd,
                    'invoice_type_code' => $recepcion->codigo_documento_fiscal,
                    'reception_code' => $recepcion->codigo_recepcion,
                ]);

                if( !$response->RespuestaServicioFacturacion->transaccion ) {
                    $errorsString = $serviceCompraVenta->getSiatErrorsString($response->RespuestaServicioFacturacion);
                    throw new \Exception("$errorsString");
                }
                $mensaeList = "";

                if ( $response->RespuestaServicioFacturacion->codigoDescripcion == "OBSERVADA")
                {
                    $mensaeList =json_encode( $response->RespuestaServicioFacturacion->mensajesList);
////                    dd($response->RespuestaServicioFacturacion);
//                    foreach ($response->RespuestaServicioFacturacion->mensajesList as $test)
//                    {
//                        dd($test);
//                    }
//                    dd($response->RespuestaServicioFacturacion->mensajesList);
                    foreach ($recepcion->facturas as $factura) {
                        $recepcion->facturas()->updateExistingPivot($factura->id, [
                            'codigo_estado' => 904,
//                            'mensaje_observacion' => 'test',
                        ]);
                    }
                }

                $recepcion->update([
                    'codigo_descripcion' => $response->RespuestaServicioFacturacion->codigoDescripcion,
                    'codigo_estado' => $response->RespuestaServicioFacturacion->codigoEstado,
                    'mensaje_observacion' => $mensaeList,
                ]);

                if( $response->RespuestaServicioFacturacion->codigoEstado == 908  ) {
                    $recepcion->facturas()->update(['estado' => 'RECEPCIONADO']);
                    foreach ($recepcion->facturas as $factura) {
                        $recepcion->facturas()->updateExistingPivot($factura->id, [
                            'codigo_estado' => 908
                        ]);
                    }
                }

                $responses[] = $response;
            }
        }catch (\Throwable $error) {
            throw new \Exception("{$error->getMessage()}", $error->getCode());
        }

        if( $eventoSignificativo->recepciones()->where('codigo_estado', '!=', 908)->doesntExist() ) {
            $eventoSignificativo->update(['estado' => 'VALIDADO']);
        }else {
            $eventoSignificativo->update(['estado' => $response->RespuestaServicioFacturacion->codigoDescripcion ]);
        }

        return $responses;
    }
    public function verifyInvoice($params)
    {
        $codigoEmision = 1;//ONLINE
        $factura = Factura::findOrFail($params['id']);

        $pos = $factura->venta->pos;
        $branchCode = $pos->sucursal->codigo_siat;
        $posCode = $pos->codigo_siat;
        $codigoDocumentoSector = $factura->codigo_documento_sector;
        $cuis = $pos->cuis->valor;
        $cufd = $pos->cuis->cufd->valor;
        $codigoTipoFactura = $factura->codigo_tipo_factura;
        $cuf = $factura->cuf;

        $response = $this->serviceCompraVenta->verifyInvoice([
            'branch_code' => $branchCode,
            'pos_code' => $posCode,
            'sector_document_code' => $codigoDocumentoSector,
            'emission_code' => $codigoEmision,
            'cuis' => $cuis,
            'cufd' => $cufd,
            'invoice_type_code' => $codigoTipoFactura,
            'cuf' => $cuf,
        ]);

        return $response;

    }


    //FACTURAS MASIVAS RECEPCION
    public function receptionElectronicInvoiceMassive($emisionMasiva)
    {
        //codigo emision offline
        $codigoEmision = EmissionCode::MASSIVE->value;
        //compra venta
        $codigoDocumentoSector = 1;
        $codigoTipoFactura = 1;


        if ($emisionMasiva->sucursal_id) {
            $cuis = $emisionMasiva->sucursal->cuis->valor;
            $cufd = $emisionMasiva->sucursal->cuis->cufd->valor;
            $branchCode = $emisionMasiva->sucursal->codigo_siat;
            $posCode = 0;
            $branchId = $emisionMasiva->sucursal->id;
            $posId = null;
        }

        if ($emisionMasiva->punto_venta_id) {
            $cuis = $emisionMasiva->pos->cuis->valor;
            $cufd = $emisionMasiva->pos->cuis->cufd->valor;
            $branchCode = $emisionMasiva->pos->sucursal->codigo_siat;
            $posCode = $emisionMasiva->pos->codigo_siat;
            $branchId = null;
            $posId = $emisionMasiva->pos->id;
        }

        // 1. Recuperar las facturas almacenadas en formato xml durante la etapa anterior
        $facturas = $emisionMasiva->facturas->toArray();

        // 2. Formar paquetes de hasta 500 facturas
        $loteFacturas = array_chunk($facturas, 1000);

//        $test = Storage::disk('private')->makeDirectory('facturas/paquetes');
        $packages = [];
        foreach ($loteFacturas as $lote) {
            $cantidadFacturas = count($lote);
            $facturaIds = [];
            $xmlFiles = [];
            $randomNameTar = uniqid('t_', true) . '.tar';
            //porciones de path
            $pathPorciones = explode('/', $lote[0]['xml']);

            $tarFilePath = Storage::disk('private')->path('facturas/'.$pathPorciones[1].'/'.$pathPorciones[2].'/'.$pathPorciones[3].'/'.$pathPorciones[4]) . '/' . $randomNameTar;

            foreach ($lote as $key => $factura) {

                $facturaIds[$factura['id']]['nro_archivo'] = $key;

                $xmlFiles[] = [
                    'path' => Storage::disk('private')->path($factura['xml']),
                    'name' => last(explode('/', $factura['xml']))
                ];

            }

            $this->tarPackFile($tarFilePath, $xmlFiles);

            try {
                $compressFilePath = $this->gzipCompressFile($tarFilePath);
            } catch (\Throwable $th) {
                throw $th;
            }

            $hash256Compress = $this->hash256($compressFilePath);

            $packages[] = [
                'numberInvoices' => $cantidadFacturas,
                'invoiceIds' => $facturaIds,
                'tarPath' => $tarFilePath,
                'gzPath' => $compressFilePath,
                'gzHash' => $hash256Compress,
            ];

        }
//        dd($packages);
        // 5.3. Enviar los paquetes de facturas
        $serviceCompraVenta = new SalePurchaseInvoice();
        $responses = [];
        foreach ($packages as $package) {

            $fecha = new DateTime();

            $handle = fopen($package['gzPath'], "r");
            $contents = fread($handle, filesize($package['gzPath']));
            fclose($handle);

            $response = $serviceCompraVenta->sendMassiveInvoice([
                'branch_code' => $branchCode,
                'pos_code' => $posCode,
                'sector_document_code' => $codigoDocumentoSector,
                'emission_code' => $codigoEmision,
                'cuis' => $cuis,
                'cufd' => $cufd,
//                'cufd' => $emisionMasiva->cufd_evento,
                'invoice_type_code' => $codigoTipoFactura,
                'file' => $contents,
                'send_date' => $fecha->format('Y-m-d\TH:i:s.v'),
                'hash_file' => $package['gzHash'],
                'number_invoices' => $package['numberInvoices'],
            ]);

            if( !$response->RespuestaServicioFacturacion->transaccion ) {
                $errorsString = $this->serviceCompraVenta->getSiatErrorsString($response->RespuestaServicioFacturacion);
                throw new \Exception("$errorsString");
            }

            // Registro recepcion
            $recepcion = Recepcion::create([
                'tipo' => ReceptionType::MASSIVE,
                'codigo_emision' => $codigoEmision,
                'fecha_envio' => $fecha->format('Y-m-d\TH:i:s.v'),
                'archivo'=>"test",
                'hash_archivo' => $package['gzHash'],
                'cantidad_facturas' => $package['numberInvoices'],
                'codigo_recepcion' => $response->RespuestaServicioFacturacion->codigoRecepcion,
                'codigo_descripcion' => $response->RespuestaServicioFacturacion->codigoDescripcion,
                'codigo_estado' => $response->RespuestaServicioFacturacion->codigoEstado,
                'codigo_documento_sector' => $codigoDocumentoSector,
                'codigo_documento_fiscal' => $codigoTipoFactura,
                'sucursal_id' => $branchId,
                'punto_venta_id' => $posId,
                'emision_masiva_id' => $emisionMasiva->id,
            ]);
            $emisionMasiva->update([
                'fecha_envio' => $fecha->format('Y-m-d\TH:i:s.v'),
                'estado' => 'RECEPCIONADO',
            ]);
            $recepcion->facturas()->attach($package['invoiceIds']);
            $responses[] = $recepcion;
        }
        return $responses;
    }

    //VALIDACION MASIVA
    public function validateMassive($emisionMasiva)
    {
        $codigoEmision = EmissionCode::MASSIVE->value;

        if($emisionMasiva->sucursal_id) {
            $cuis = $emisionMasiva->sucursal->cuis->valor;
            $cufd = $emisionMasiva->sucursal->cuis->cufd->valor;
            $branchCode = $emisionMasiva->sucursal->codigo_siat;
            $posCode = 0;
        }

        if($emisionMasiva->punto_venta_id) {
            $cuis = $emisionMasiva->pos->cuis->valor;
            $cufd = $emisionMasiva->pos->cuis->cufd->valor;
            $branchCode = $emisionMasiva->pos->sucursal->codigo_siat;
            $posCode = $emisionMasiva->pos->codigo_siat;
        }

        $serviceCompraVenta = new SalePurchaseInvoice();

        $responses = [];
        foreach ($emisionMasiva->recepciones->where('emision_masiva_id',$emisionMasiva->id) as $recepcion) {

            if( $recepcion->codigo_estado == 908 ) {
                continue;
            }

            $response = $serviceCompraVenta->validateReceptionMassiveInvoice([
                'branch_code' => $branchCode,
                'pos_code' => $posCode,
                'sector_document_code' => $recepcion->codigo_documento_sector,
                'emission_code' => $codigoEmision,
                'cuis' => $cuis,
                'cufd' => $cufd,
                'invoice_type_code' => $recepcion->codigo_documento_fiscal,
                'reception_code' => $recepcion->codigo_recepcion,
            ]);

            if( !$response->RespuestaServicioFacturacion->transaccion ) {
                $errorsString = $serviceCompraVenta->getSiatErrorsString($response->RespuestaServicioFacturacion);
                throw new \Exception("$errorsString");
            }

            $recepcion->update([
                'codigo_descripcion' => $response->RespuestaServicioFacturacion->codigoDescripcion,
                'codigo_estado' => $response->RespuestaServicioFacturacion->codigoEstado,
            ]);

            if( $response->RespuestaServicioFacturacion->codigoEstado == 908  ) {
                $recepcion->facturas()->update(['estado' => 'RECEPCIONADO']);
                foreach ($recepcion->facturas as $factura) {
                    $recepcion->facturas()->updateExistingPivot($factura->id, [
                        'codigo_estado' => 908
                    ]);
                }
            }

            $responses[] = $response;
        }

        if( $emisionMasiva->recepciones()->where('codigo_estado', '!=', 908)->doesntExist() ) {
            $emisionMasiva->update(['estado' => 'VALIDADO']);
        }
        else {
            $emisionMasiva->update(['estado' => $response->RespuestaServicioFacturacion->codigoDescripcion ]);
        }

        return $responses;

    }
    public function communicationVerification()
    {
        $serviceCompraVenta = new SalePurchaseInvoice();
        $response = $serviceCompraVenta->communicationVerification();
        return $response;
    }
}
