<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\StoreUpdateDetalleProformaRequest;
use App\Http\Requests\StoreUpdateProformaRequest;
use App\Http\Resources\ProductosBuscadosResource;
use App\Http\Resources\ProformaResource;
use App\Http\Services\Codigos\VerificacionNitService;
use App\Http\Services\FacturaService;
use App\Http\Traits\ApiResponser;
use App\Models\AutorizacionSistema;
use App\Models\Cliente;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ValorCatalogo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Luecano\NumeroALetras\NumeroALetras;

class ProformaController extends Controller
{
    use ApiResponser;
    public function __construct()
    {
        $this->middleware(['permission:vista-proformas','permission:listar-proforma'])->only('index');
        $this->middleware(['permission:vista-proformas','permission:crear-proforma'])->only('store');
        $this->middleware(['permission:vista-proformas','permission:editar-proforma'])->only('update');
        $this->middleware(['permission:vista-proformas','permission:eliminar-proforma'])->only('destroy');
        $this->middleware(['permission:vista-proformas','permission:ver-proforma'])->only('show');
        $this->middleware('permission:crear-venta')->only('buscarProductoInventario');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date'
        ]);
        $perPage = request('per_page', 20);
        $fechaInicio =Carbon::parse( request('fecha_inicio',Carbon::now()))->format('Y-m-d 00:00:00');
        $fechaFin = Carbon::parse(request('fecha_fin',Carbon::now()))->format('Y-m-d 23:59:59');
        $search = request('search',null);
        try {
            $message = "Registros recuperados correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            return ProformaResource::collection(
                Proforma::whereHas('cliente', function ($query) use($search){
                    $query->whereLike(['razon_social','cedula_nit'], $search);
                })->with('cliente')->orWhere(function ($query) use ($search) {
                        if(is_numeric($search))
                        {
                            $query->where( 'codigo_secuencia',intval($search));
                        }
                }) ->when($search, function ($query, $search) {
                    // Si $search tiene un valor, no se busca por fecha
                    return $query;
                }, function ($query) use ($fechaInicio, $fechaFin) {
                    // Si $search no tiene un valor, se busca por fecha
                    return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
                })
                    ->orderBy('codigo_secuencia')
                    ->get()
            )->additional($dataAdditional);

        } catch (\Throwable $error) {
            $message = "Registro no recuperados!!";
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }

    }
    public function buscarProductoInventario (Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursales,id',
            'producto_id' => 'required',
        ]);
        $idSucursal = request('sucursal_id',1);
        $idsProductos = request('producto_id');

        try {
            $data = [];
            foreach ($idsProductos as $id)
            {
                $dataInventario = Inventario::where('sucursal_id',$idSucursal)->where('producto_id',$id)->pluck('id','producto_id');
                $data[] = $dataInventario->all();

            }
            return response()->json([
                'message' => 'Correcto',
                'data' => $data,
                'success' => true
            ]);
        } catch (\Throwable $error) {
            $message = "Registro no recuperados!!";
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUpdateProformaRequest $requestProforma, StoreUpdateDetalleProformaRequest $requestDetalleProforma, StoreClienteRequest $requestCliente)
    {
        $dataProforma = $requestProforma->validated();
        $dataDetalleProforma = $requestDetalleProforma->validated();
        $dataCliente = $requestCliente->validated()['datosCliente'];
        DB::beginTransaction();
        try {
            //secuencia de la proforma
            $maxSecuencia = Proforma::where('sucursal_id', $dataProforma['sucursal_id'])->max('codigo_secuencia');
            $numeroSecuencial = $maxSecuencia + 1;
            $dataProforma['codigo_secuencia'] = $numeroSecuencial;
            //fecha Registro
            $fechaRegistro = Carbon::now()->format('Y-m-d\TH:i:s.v');
            $dataProforma['fecha']=$fechaRegistro;
            //registro ccliente
            $idCliente = $dataProforma['cliente_id'];
            if($idCliente == 0)
            {
                $cliente = $this->registroCliente($dataCliente,$dataProforma['sucursal_id']);
                $dataProforma['cliente_id'] = $cliente->id;
            }else{
                Cliente::where('id', $dataProforma['cliente_id'])
                    ->update(['razon_social' => $dataCliente['razon_social']]);
            }

            $sumaSubTotalValidacion = 0;
            foreach ($dataDetalleProforma['detalleProforma'] as $detalleProforma) {
                $subTotal = $detalleProforma['sub_total'];
                $sumaSubTotalValidacion += $subTotal;
            }
            $dataProforma['descuentoMonto'] =  round((($sumaSubTotalValidacion*$dataProforma['descuento'])/100),2);

            /*Creacion de venta*/
            $proforma = Proforma::create($dataProforma);
            /* registro de los item profomra*/

            foreach ($dataDetalleProforma['detalleProforma'] as $detalleProforma) {
                $cantidad = $detalleProforma['cantidad'];
                $precio = $detalleProforma['precio'];
                $subTotal = $detalleProforma['sub_total'];
                $descuento = $detalleProforma['descuento'];

                $codigoProductoMayorMenor =$detalleProforma ['codigo_producto_mayor_menor'];
                $descuentoMonto = $this->calcularDescuento($subTotal,$descuento);

                $proforma->productos()->attach($detalleProforma['producto_id'], [
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'descuentoMonto' => $descuentoMonto,
                    'sub_total' => $subTotal,
                    'codigo_producto_mayor_menor' => $codigoProductoMayorMenor
                ]);
            }

            $sumaSubTotalValidacion = $sumaSubTotalValidacion - $dataProforma['descuentoMonto'] ;

            if(round($sumaSubTotalValidacion,2) != round($proforma->total,2))
            {
                throw new \Exception("Los montos de detalle sub total y total vendido no corresponden");
            }
            DB::commit();
            $message = "Se registro correctamente!!";
            return  $this->CreatedResponse($proforma, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollback();
            $message = "Registro fallido";
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
//        dd($dataProforma,$dataDetalleProforma);
    }
    public function calcularDescuento($subTotal, $porcentajeDescuento)
    {
            return round(($subTotal*$porcentajeDescuento)/(100-($porcentajeDescuento)),2);
    }

    public function registroCliente($dataCliente,$idSucursal)
    {
        $dataCliente['departamento_id']=1;
        $codigoDocumentoId = ValorCatalogo::find($dataCliente['tipo_documento_id'])['codigo_clasificador'];

        $responseVerificacion = $this->verificacionComunicacionSiat();
        $codeStatus =$responseVerificacion->getStatusCode();
        $dataCliente['sucursal_id'] = $idSucursal;

        if($codigoDocumentoId == '5' && $codeStatus == 200)
        {
            $verificacionNitService = new VerificacionNitService();
            $verificacionNitService->handleStore($dataCliente);
            $dataCliente['verificacion']=1;
        }
        $cliente = Cliente::create($dataCliente);
        return $cliente;
    }
    public function verificacionComunicacionSiat()
    {
        try {
            $facturaService = new FacturaService();
            $response = $facturaService->communicationVerification();
            $message = "verificado";
            return $this->ResponseJson($response,$message);
        }catch (\Throwable $error) {

            $message = 'Obteción de datos fallida.';
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Proforma  $proforma
     * @return \Illuminate\Http\Response
     */
    public function show(Proforma $proforma)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return ProformaResource::make($proforma->load('cliente','sucursal','productos'))->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proforma  $proforma
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUpdateProformaRequest $requestProforma, StoreUpdateDetalleProformaRequest $requestDetalleProforma, Proforma $proforma)
    {
        $dataProforma = $requestProforma->validated();
        $dataDetalleProforma = $requestDetalleProforma->validated();


        DB::beginTransaction();
        try {

            $sumaSubTotalValidacion = 0;
            foreach ($dataDetalleProforma['detalleProforma'] as $detalleProforma) {
                $subTotal = $detalleProforma['sub_total'];
                $sumaSubTotalValidacion += $subTotal;
            }
            $dataProforma['descuentoMonto'] =  round((($sumaSubTotalValidacion*$dataProforma['descuento'])/100),2);

            /*Update de compra  a la tabla producto*/
            $proforma->update($dataProforma);
            //elimina todos los productos con detach
            $proforma->productos()->detach();

            /* update de los itemsCompras*/
            foreach ($dataDetalleProforma['detalleProforma'] as $detalleProforma) {
                $cantidad = $detalleProforma['cantidad'];
                $precio = $detalleProforma['precio'];
                $subTotal = $detalleProforma['sub_total'];
                $descuento = $detalleProforma['descuento'];

                $descuentoMonto = $this->calcularDescuento($subTotal,  $descuento);
                $codigoProductoMayorMenor = $detalleProforma ['codigo_producto_mayor_menor'];


                $proforma->productos()->attach($detalleProforma['producto_id'], [
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'descuento' => $descuento,
                    'descuentoMonto' => $descuentoMonto,
                    'sub_total' => $subTotal,
                    'codigo_producto_mayor_menor' => $codigoProductoMayorMenor
                ]);
            }

            $sumaSubTotalValidacion = $sumaSubTotalValidacion-$dataProforma['descuentoMonto'];
            if(round($sumaSubTotalValidacion,2) != round($proforma->total,2))
            {
                throw new \Exception("Los montos de detalle sub total y total vendido no corresponden");
            }
            DB::commit();
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($proforma, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollback();
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Proforma  $proforma
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proforma $proforma)
    {
        try {
            if ($proforma->estado == 'ACTIVO') {
                $proforma->update(['estado' => 'ANULADO']);
                $message = "Proforma Nro. ".$proforma->codigo_secuencia." anulado correctamente!!";
            }else if($proforma->estado == 'ANULADO'){
                $proforma->update(['estado' => 'ACTIVO']);
                $message = "Proforma Nro. ".$proforma->codigo_secuencia." activado correctamente!!";
            }
            return $this->SuccessResponse($message,Response::HTTP_NO_CONTENT);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }

    }

    public function buscarProductos(Request $request)
    {

        $search = $request->query('search',null);
        $codigoBarra = $request->query('codigoBarra',"false");
        $precioMayor = $request->query('precioMayor', "false");
        $idSucursal = $request->query('sucursal_id', 1);
        try {
            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);

            $productos = Producto::whereHas('precioGeneral', function($query) use ($precioMayor){
                $query->where($precioMayor === "true" ? 'precio_mayor' : 'precio_menor', '>', 0);
            })->whereLike(['descripcion'], $search)->orderBy('id','ASC')->get();

            return ProductosBuscadosResource::collection($productos)->additional($dataAdditional);

            if($codigoBarra === "true")
            {
//                return InventarioProductoVentaResource::collection(
//                    Inventario::whereHas('producto',function (Builder $query) use  ($search) {
//                        $query->where('codigo_barra', $search);
//                    })->whereHas('producto.clasificacionProducto',function ($query){
//                        $query->where('id',2);
//                    })->where('sucursal_id',$idSucursal)->where('cantidad','>',0)->paginate(20)
//                )->additional($dataAdditional);
            }
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    public function reporte(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:proformas,id',
        ]);
        $logoUrl = AutorizacionSistema::first()['logo'];
//        $logo =  URL::to(Storage::url($logoUrl));
        $logo = 'storage/'.$logoUrl;
//        dd($logo);
//        $logo = public_path($logoUrl);
//            dd($logo,$test);
        $idProforma = request('id');
        $datosProforma = Proforma::with('productos.unidadMedida.valorCatalogo','cliente','sucursal')->find($idProforma);

        $formatter = new NumeroALetras();
        $numeroLiteral = $formatter->toInvoice($datosProforma->total,2,'BOLIVIANOS');
        $patron = "CON";
        // Reemplazar la palabra "CON" por una cadena vacía
        $texto_resultante = str_replace($patron, "", $numeroLiteral);
        // Eliminar cualquier espacio en blanco al final del texto resultante
        $numeroLiteral = trim($texto_resultante);

        $pdf = PDF::loadView('/reports/proformas/reportePagina',compact('datosProforma','numeroLiteral','logo'))->setPaper('letter');
//
        return $pdf->stream("Proforma {$idProforma}.pdf");
    }
}
