<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\StoreUpdateDetalleVentaRequest;
use App\Http\Requests\StoreUpdateEmisionMasivaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\EmisionMasivaResource;
use App\Http\Services\CufdService;
use App\Http\Services\FacturaService;
use App\Http\Traits\ApiResponser;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\EmisionMasiva;
use App\Models\EventoSignificativo;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmisionMasivaFacturaController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $perPage = request('per_page', 20);
        $idPuntoVenta = request('pos_id');
        try {
            $dataAdicional = $this->SuccessResponse('Registro recuperado correctamente!');
            return EmisionMasivaResource::collection(EmisionMasiva::where('punto_venta_id',$idPuntoVenta)->paginate($perPage)
            )->additional($dataAdicional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obteción de datos fallida!', $error, Response::HTTP_BAD_REQUEST);
        }

    }

    public function store(StoreUpdateEmisionMasivaRequest $request)
    {

        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();
            $dataEmisionMasiva = EmisionMasiva::where('punto_venta_id',$dataValidated['punto_venta_id'])->where('estado','INICIADO')->first();

            if ($dataEmisionMasiva)
            {
                throw new \Exception("Debe recepcionar la emision INICIADA o VALIDADA antes de crear uno nuevo.");
            }
//            //nuevo cufd para envio de paquetes
//            $cufdService = new CufdService();
//            $cufdService->handleStore(['cuis_id' => $idCuis]);

            if( $request->has('sucursal_id') ) {
                $sucursal = Sucursal::findOrFail($request->sucursal_id);
                if( !( $cufd = $sucursal->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para la sucursal {$sucursal->nombres}");
                }
            }

            if( $request->has('punto_venta_id') ) {
                $pos = PuntoVenta::findOrFail($request->punto_venta_id);

                if( !( $cufd = $pos->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para el punto de venta {$pos->nombre}");
                }
            }

            $dataValidated['cufd_evento'] = $cufd->valor;

            $data = EmisionMasiva::create($dataValidated);

            DB::commit();
            $message = "Se registro correctamente!";
            return $this->CreatedResponse( EmisionMasivaResource::make(EmisionMasiva::find($data->id)), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);

        }


    }
    public function show (EmisionMasiva $emisionMasiva)
    {
        try {
            $dataAdicional = $this->SuccessResponse('Registro recuperado correctamente!');
            return EmisionMasivaResource::make($emisionMasiva->load('facturas.venta','recepciones','pos'))->additional($dataAdicional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obteción de datos fallida!', $error, Response::HTTP_BAD_REQUEST);
        }
    }

    public function receptionInvoiceMassive(EmisionMasiva $emisionMasiva)
    {
        DB::beginTransaction();
        try {
            if( in_array($emisionMasiva->estado, ['RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("La emision masiva ya fue recepcionado y/o validado", 405);
            }

            //si no hay facturas que enviar
            if($emisionMasiva->facturas->isEmpty() && in_array($emisionMasiva->evento->codigo_clasificador,[ 5, 6,7]) )
            {
                throw new \Exception("Debe transcribir facturas antes de enviar el paquete!!");
            }

            if($emisionMasiva->sucursal_id) {
                $sucursal = Sucursal::findOrFail($emisionMasiva->sucursal_id);
                $idCuis = $emisionMasiva->sucursal->cuis->id;
                if( !( $cufd = $sucursal->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para la sucursal {$sucursal->nombres}");
                }
            }

            if( $emisionMasiva->punto_venta_id ) {
                $pos = PuntoVenta ::findOrFail($emisionMasiva->punto_venta_id);
                $idCuis = $emisionMasiva->pos->cuis->id;
                if( !( $cufd = $pos->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para el punto de venta {$pos->nombre}");
                }
            }

            //nuevo cufd para envio de paquetes
//            $cufdService = new CufdService();
//            $cufdService->handleStore(['cuis_id' => $idCuis]);


            $facturaService = new FacturaService();
            $response = $facturaService->receptionElectronicInvoiceMassive($emisionMasiva);
            DB::commit();
            $message = "Se recepciono correctamente!";
            return $this->ResponseJson($response,$message);
//            return $this->CreatedResponse(Recepci  EventoSignificativoResource($data), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = 'Registro fallido!';
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }


    }

    public function validateReceptionInvoiceMassive(EmisionMasiva $emisionMasiva)
    {
        DB::beginTransaction();
        try {

            if( in_array($emisionMasiva->estado, ['INICIADO','VALIDADO']) ) {
                throw new \Exception("El evento debe estar recepcionado", 405);
            }

            $eventService = new FacturaService();
            $response = $eventService->validateMassive($emisionMasiva);
            DB::commit();
            return $this->CreatedResponse($response, 'Se valido correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            throw $error;
            return $this->ErrorResponse('Validacion de evento fallido!', $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }


    }

    public function transcribe(StoreVentaRequest $requestVenta, StoreUpdateDetalleVentaRequest $requestDetalleVenta, StoreClienteRequest $requestCliente, EmisionMasiva $emisionMasiva)
    {
        $dataVenta = $requestVenta->validated();
        $dataDetalleVenta = $requestDetalleVenta->validated();

        $egresoMovimiento = 4;
        DB::beginTransaction();
        try {
            $date = Carbon::parse($dataVenta['fecha']);

//            if ( ! $date->between($emisionMasiva->fecha_inicio, $emisionMasiva->fecha_fin) ) {
//                throw new \Exception("La fecha de venta debe estar entre {$emisionMasiva->fecha_inicio} y {$emisionMasiva->fecha_fin}");
//            }
            //secuencia
            $secuencia = Venta::where('sucursal_id',$dataVenta['sucursal_id'])->where('punto_venta_id',$dataVenta['punto_venta_id'])->count();
            //sumar
            $numeroSecuencial = 1;
            $numeroSecuencial += $secuencia;
            $dataVenta['codigo_secuencia'] = $numeroSecuencial;

            $origen = "ventas";
            //creacion nuevo cliente si no existe
            if(isset($requestCliente->validated()['datosCliente']) === true)
            {
                $dataCliente = $requestCliente->validated()['datosCliente'];
                $dataCliente['departamento_id']=1;
                $cliente = Cliente::create($dataCliente);
                $dataVenta['cliente_id']=$cliente->id;
            }
            $dataVenta['descuento'] = round(($dataVenta['total']*$dataVenta['descuento'])/(100-($dataVenta['descuento'])),2);

            /*Creacion de venta*/
            $venta = Venta::create($dataVenta);

            /* registro de los itemsVentas*/
            $sumaSubTotalValidacion = 0;

            foreach ($dataDetalleVenta['detalleVenta'] as $detalleVenta) {
                $sumaSubTotalValidacion += $detalleVenta['sub_total'];
                //monto descuento
                $detalleVenta['descuento'] = round(($detalleVenta['sub_total']*$detalleVenta['descuento'])/(100-($detalleVenta['descuento'])),2);

                $venta->inventarios()->attach($detalleVenta['inventario_id'], [
                    'cantidad' => $detalleVenta['cantidad'],
                    'precio' => $detalleVenta['precio'],
                    'descuento' => $detalleVenta['descuento'],
                    'sub_total' => $detalleVenta['sub_total']
                ]);

                $dataInventario = Inventario::where('id',$detalleVenta['inventario_id'])->where('sucursal_id',$venta->sucursal_id)->first();

                InventarioMovimiento::create([
                    'inicial' => 0,
                    'ingresos' =>0,
                    'egresos' => $detalleVenta['cantidad'],
                    'precio' => $detalleVenta['precio'],
                    'identificador' => $venta->id,
                    'origen' => $origen,
                    'secuencial_origen' => $venta->id,
                    'observaciones' => null,
                    'fecha' => Carbon::now(),
                    'movimiento_id' => $egresoMovimiento,
                    'inventario_id' =>$dataInventario->id
                ]);

//            //incrementar la cantidad en inventario
                Inventario::find($dataInventario->id)->decrement('cantidad',$detalleVenta['cantidad']);
            }

            //comprovacion de montos detalle y venta total
            $sumaSubTotalValidacion = $sumaSubTotalValidacion-$dataVenta['descuento'];

            if(round($sumaSubTotalValidacion,2) != round($venta->total,2))
            {
                throw new \Exception("Los montos de detalle sub total y total vendido no corresponden");
            }

            $facturaService = new FacturaService();

            $cufd = Cufd::where('valor', $emisionMasiva->cufd_evento)->first();
//            dd($cufd,$emisionMasiva->cufd_evento);
            $params = [
                'fecha' => $venta->fecha,
                'branchCode' => $venta->sucursal->codigo_siat,
                'posCode' => $venta->pos->codigo_siat,
                'codigoControl' => $venta->pos->cuis->cufd->codigo_control,
                'departamento' => $venta->sucursal->departamento->departamento,
                'telefono' => $venta->sucursal->telefono,
                'direccion' => $venta->sucursal->direccion,
                'cufd' =>  $cufd->valor, //$venta->pos->cuis->cufd->valor,
                'cufdId' => $cufd->id //$venta->pos->cuis->cufd->id,
            ];

            $factura = $facturaService->createInvoice($venta->load(['inventarios.producto' => ['homologacion', 'atributos'],]), $params,null,$emisionMasiva);

            $facturaService->emitMasive($factura, $emisionMasiva->id);

            DB::commit();
            return $this->CreatedResponse($venta->id, 'Se registro correctamente', Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            return $this->ErrorResponse('Registro fallido!', $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
