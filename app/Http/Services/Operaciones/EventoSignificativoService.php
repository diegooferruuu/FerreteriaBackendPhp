<?php

namespace App\Http\Services\Operaciones;

use App\Enums\EmissionCode;
use App\Enums\ReceptionType;
use App\Http\Services\Siat\Operation;
use App\Http\Services\Siat\SalePurchaseInvoice;
use App\Http\Traits\Siat\CompressFile;
use App\Http\Traits\Siat\Hasher;
use App\Models\EventoSignificativo;
use App\Models\Recepcion;
use DateTime;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion de evento significativo.
 */
class EventoSignificativoService
{
    use CompressFile, Hasher;

    /**
     * Instancia de servicio de operaciones
     *
     * @var Operation
     */
    protected $serviceOperation;

    public function __construct()
    {
        $this->serviceOperation = new Operation();
    }

    public function getEventSignificat($eventoSignificativo)
    {

        if($eventoSignificativo->sucursal_id) {
            $cuis = $eventoSignificativo->sucursal->cuis->valor;
            $cufd = $eventoSignificativo->sucursal->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->sucursal->codigo_siat;
            $posCode = 0;
        }

        if($eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->pos->cuis->valor;
            $cufd = $eventoSignificativo->pos->cuis->cufd->valor;
            $branchCode = null; //  $eventoSignificativo->pos->sucursal->codigo_siat;
            $posCode = $eventoSignificativo->pos->codigo_siat;
            $branchId = null;
        }

        $params = [
            'cuis' => $cuis,
            'cufd' => $cufd,
            'branch_code' => $branchCode,
            'pos_code' => $posCode,
            'fecha_inicio' => dateFormatUtcExtended($eventoSignificativo->fecha_inicio),
        ];

        $response = $this->serviceOperation->getEvent($params);
        return $response;

    }

    public function registerEventSignificant($eventoSignificativo)
    {

        //facturas emitidas $eventoSignificativo->loadCount('facturas')->facturas_count
        if($eventoSignificativo->sucursal_id && $eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->sucursal->cuis->valor;
            // Generar cufd una vez superada el evento (cufd actual)
            $cufd = $eventoSignificativo->sucursal->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->sucursal->codigo_siat;
            $posCode = 0;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = $eventoSignificativo->sucursal->id;
            $posId = null;
        }

        if($eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->pos->cuis->valor;
            // Generar cufd una vez superada el evento
            $cufd = $eventoSignificativo->pos->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->pos->sucursal->codigo_siat;
            $posCode = $eventoSignificativo->pos->codigo_siat;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = null;
            $posId = $eventoSignificativo->pos->id;
        }
        $fechaFinEvento = dateFormatUtcExtended($eventoSignificativo->fecha_fin);
//        dd($eventoSignificativo->cufd_evento);
        $params = [
            'cuis' => $cuis,
            'cufd' => $cufd,
            'cufd_evento' => $eventoSignificativo->cufd_evento,
            'branch_code' => $branchCode,
            'pos_code' => $posCode,
            'event_code' => $eventCode,
            'descripcion' => $eventoSignificativo->descripcion,
            'fecha_inicio' => dateFormatUtcExtended($eventoSignificativo->fecha_inicio),
            'fecha_fin' => $fechaFinEvento,
        ];

        try {
            $responseRegister= $this->serviceOperation->registerEvent($params);
        }catch (\Throwable $error) {
            throw new \Exception("{$error->getMessage()}", $error->getCode());
        }


        if( !$responseRegister->transaccion ) {
            throw new \Exception("CODE: {$responseRegister->mensajesList->codigo}, {$responseRegister->mensajesList->descripcion}");
        }
        $eventoSignificativo->update([
            'codigo_recepcion' => $responseRegister->codigoRecepcionEventoSignificativo,
            'fecha_fin' =>$fechaFinEvento,
            'estado' => 'FINALIZADO',//determinar
        ]);

    }

    /**
     * Registrar evento significativo y enviar paquete de facturas a SIAT.
     *
     * @param EventoSignificativo $eventoSignificativo  Evento registrado en nuestra base de datos.
     * @return void
     */
    public function register(EventoSignificativo $eventoSignificativo)
    {
        $codigoEmision = EmissionCode::OFFLINE->value;
        $codigoDocumentoSector = 1;
        $codigoTipoFactura = 1;

        $eventoSignificativo->loadCount('facturas');

        if( $eventoSignificativo->facturas_count == 0 ) {
            throw new \Exception("Ninguna factura emitida durante el evento significativo.");
        }

        if($eventoSignificativo->sucursal_id) {
            $cuis = $eventoSignificativo->sucursal->cuis->valor;
            // Generar cufd una vez superada el evento (cufd actual)
            $cufd = $eventoSignificativo->sucursal->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->sucursal->codigo_siat;
            $posCode = 0;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = $eventoSignificativo->sucursal->id;
            $posId = null;
        }

        if($eventoSignificativo->punto_venta_id) {
            $cuis = $eventoSignificativo->pos->cuis->valor;
            // Generar cufd una vez superada el evento
            $cufd = $eventoSignificativo->pos->cuis->cufd->valor;
            $branchCode = $eventoSignificativo->pos->sucursal->codigo_siat;
            $posCode = $eventoSignificativo->pos->codigo_siat;
            $eventCode = $eventoSignificativo->evento->codigo_clasificador;
            $branchId = null;
            $posId = $eventoSignificativo->pos->id;
        }
        // 5. Envio de paquetes de factura
        // 5.1. Obtener un nuevo CUFD

        // 5.2. Registrar el evento significativo
        if( $eventoSignificativo->estado == 'FINALIZADO' ) {
            $params = [
                'cuis' => $cuis,
                'cufd' => $cufd,
                'cufd_evento' => $eventoSignificativo->cufd_evento,
                'branch_code' => $branchCode,
                'pos_code' => $posCode,
                'event_code' => $eventCode,
                'descripcion' => $eventoSignificativo->descripcion,
                'fecha_inicio' => dateFormatUtcExtended($eventoSignificativo->fecha_inicio),
                'fecha_fin' => dateFormatUtcExtended($eventoSignificativo->fecha_fin),
            ];

            $responseRegister = $this->serviceOperation->registerEvent($params);

            if( !$responseRegister->transaccion ) {
                throw new \Exception("CODE: {$responseRegister->mensajesList->codigo}, {$responseRegister->mensajesList->descripcion}");
            }

            $eventoSignificativo->update([
                'codigo_recepcion' => $responseRegister->codigoRecepcionEventoSignificativo,
                'estado' => 'RECEPCIONADO',
            ]);
        }

        // Segunda etapa
        // 1. Recuperar las facturas almacenadas en formato xml durante la etapa anterior
        $facturas = $eventoSignificativo->facturas->toArray();

        // 2. Formar paquetes de hasta 500 facturas
        $loteFacturas = array_chunk($facturas, 500);

        Storage::disk('private')->makeDirectory('facturas/paquetes');
        $packages = [];
        foreach ($loteFacturas as $lote) {
            $cantidadFacturas = count($lote);
            $facturaIds = [];
            $xmlFiles = [];
            $randomNameTar = uniqid('t_', true) . '.tar';
            $tarFilePath = Storage::disk('private')->path('facturas/paquetes') . '/' . $randomNameTar;

            foreach ($lote as $key => $factura) {

                $facturaIds[$factura['id']]['nro_archivo'] = $key;

                $xmlFiles[] = [
                    'path' => Storage::disk('private')->path($factura['xml']),
                    'name' => last( explode('/', $factura['xml']) )
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

        // 5. Envio de paquete de facturas

        // 5.3. Enviar los paquetes de facturas
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
                'cafc' => null,
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

            $recepcion->facturas()->attach($package['invoiceIds']);
        }
    }

    /**
     * Validar paquete de facturas recepcionadas, que fueron emitidas durente el evento significativo
     *
     * @param EventoSignificativo $eventoSignificativo
     * @return array
     */
    public function validate(EventoSignificativo $eventoSignificativo)
    {
        $codigoEmision = 2;

        if($eventoSignificativo->sucursal_id) {
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

        foreach ($eventoSignificativo->recepciones as $recepcion) {

            if( $recepcion->codigo_estado == 908) {
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

        if( $eventoSignificativo->recepciones()->where('codigo_estado', '!=', 908)->doesntExist() ) {
            $eventoSignificativo->update(['estado' => 'VALIDADO']);
        }

        return $responses;
    }
}
