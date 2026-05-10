<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\FacturaService;
use App\Http\Services\Operaciones\EventoSignificativoService;
use App\Http\Traits\ApiResponser;
use App\Models\EventoSignificativo;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Services\CufdService;

class EmisionPaqueteFacturaController extends Controller
{
    use ApiResponser;

    public function receptionElectronicInvoicePackage(EventoSignificativo $eventoSignificativo)
    {
        DB::beginTransaction();
        try {
            if( in_array($eventoSignificativo->estado, ['INICIADO','RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("El evento significativo ya fue recepcionado y/o validado", 405);
            }

            //si no hay facturas que enviar
            if($eventoSignificativo->facturas->isEmpty() && in_array($eventoSignificativo->evento->codigo_clasificador,[ 5, 6,7]) )
            {
                throw new \Exception("Debe transcribir facturas antes de enviar el paquete!!");
            }

            if($eventoSignificativo->sucursal_id && $eventoSignificativo->punto_venta_id) {
                $sucursal = Sucursal::findOrFail($eventoSignificativo->sucursal_id);
                $idCuis = $eventoSignificativo->sucursal->cuis->id;
                if( !( $cufd = $sucursal->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para la sucursal {$sucursal->nombres}");
                }
            }

            if( $eventoSignificativo->punto_venta_id ) {
                $pos = PuntoVenta ::findOrFail($eventoSignificativo->punto_venta_id);
                $idCuis = $eventoSignificativo->pos->cuis->id;
                if( !( $cufd = $pos->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para el punto de venta {$pos->nombre}");
                }
            }

            $facturaService = new FacturaService();
            $response = $facturaService->receptionElectronicInvoicePackage($eventoSignificativo);

//            $this->validateReceptionInvoicePackage($eventoSignificativo);

            DB::commit();
            $message = "Se recepciono correctamente!";
            return $this->ResponseJson($response,$message);
//            return $this->CreatedResponse(Recepci  EventoSignificativoResource($data), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();

            $message = "Registro fallido";
            $statusCode = Response::HTTP_PRECONDITION_FAILED;
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            if ($error->getCode() == 503)
            {
                $statusCode = $error->getCode();
            }
            return $this->ErrorResponse($message, $error, $statusCode) ;
        }
    }

    public function validateReceptionInvoicePackage(EventoSignificativo $eventoSignificativo) {
        DB::beginTransaction();
        try {

            if( in_array($eventoSignificativo->estado, ['INICIADO', 'FINALIZADO','VALIDADO','OBSERVADA']) ) {
                throw new \Exception("El evento debe estar recepcionado", 405);
            }

            $eventService = new FacturaService();
            $response = $eventService->validate($eventoSignificativo);
            DB::commit();
            return $this->CreatedResponse($response, 'Se valido correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();

            $message = "Registro fallido";
            $statusCode = Response::HTTP_PRECONDITION_FAILED;
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            if ($error->getCode() != 0)
            {
                $statusCode = $error->getCode();
            }
            return $this->ErrorResponse($message,$error, $statusCode);
        }
    }
}
