<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArrayDetalleVentaRequest;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\StoreEventoSignificativoRequest;
use App\Http\Requests\StoreUpdateDetalleVentaRequest;
use App\Http\Requests\StoreVentaBackRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateEventoSignificativoRequest;
use App\Http\Resources\EventoSignificativoResource;
use App\Http\Services\CufdService;
use App\Http\Services\FacturaService;
use App\Http\Services\Operaciones\EventoSignificativoService;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\EstadoSistema;
use App\Http\Traits\Siat\Cuf;
use App\Mail\SendInvoice;
use App\Models\AutorizacionSistema;
use App\Models\Cafc;
use App\Models\Cliente;
use App\Models\Cufd;
use App\Models\EventoSignificativo;
use App\Models\Factura;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventoSignificativoController extends Controller
{
    use ApiResponser, EstadoSistema;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $estadoSistema;
    public function __construct()
    {
        $this->middleware('permission:evento-significativo')->except('');
    }


    public function index()
    {

        $idPuntoVenta = request('pos_id');
//        dd($idPuntoVenta);
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctamente!');
        return EventoSignificativoResource::collection(EventoSignificativo::withCount('facturas')->where('punto_venta_id',$idPuntoVenta)->paginate($perPage))->additional($dataAdditional);
    }
    public function getRecordsSiat()
    {

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreEventoSignificativoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventoSignificativoRequest $request)
    {
        $dataValidated = $request->validated();
        DB::beginTransaction();
        try {
            $fechaInicio = Carbon::parse($dataValidated['fecha_inicio']);
            $fechaCreate = '';
//            dd($fechaInicio);
            if( $request->has('sucursal_id') ) {
                $sucursal = Sucursal::findOrFail($request->sucursal_id);

                $fechaCreate= $sucursal->cuis->cufd->created_at;
                if($sucursal->inEvent == true)
                {
                    throw new \Exception("No se pudo registrar!!, debe enviar el evento en curso para su validación antes de registrar otro evento.");
                }
                $dataValidated['punto_venta_id'] = $sucursal->cuis?->punto_venta_id;
                if( !( $cufd = $sucursal->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para la sucursal {$sucursal->nombres}");
                }
            }

            if( $request->has('punto_venta_id') ) {
                $pos = PuntoVenta::findOrFail($request->punto_venta_id);
                $fechaCreate= $pos->cuis->cufd->created_at;
                if($pos->inEvent == true)
                {
                    throw new \Exception("No puede se pudo registrar, debe enviar el evento en curso para su validación!!");
                }
                if( !( $cufd = $pos->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para el punto de venta {$pos->nombre}");
                }
            }



            //evento
            $evento = ValorCatalogo::where('id',$dataValidated['evento_id'])->first()['codigo_clasificador'];
//            dd($evento);
            if (in_array($evento,[1,2,3,4]))
            {
                if (!$fechaInicio->greaterThan($fechaCreate)) {
                    $fechaCreate = Carbon::parse($fechaCreate)->addMinute()->setSecond(0)->format('d-m-Y H:i');
                    throw new \Exception("La fecha de inicio no puede ser menor que la fecha de creacion de cufd, espere un momento!! fecha sugerida {$fechaCreate}");
                }
            }
            if ($dataValidated['cufd_id'] == 0 || in_array($evento,[1,2,3,4]) )
            {

                $dataValidated['cufd_evento'] = $cufd->valor;
            }else{
                //cufd del evento en fecha seleccionada
                $cufdDataEvento = Cufd::where('id',$dataValidated['cufd_id'])->first();
                $dataValidated['cufd_evento'] = $cufdDataEvento['valor'];

                $fechaInicio = Carbon::parse($dataValidated['fecha_inicio'])->format('Y-m-d H:i');
                $fechaCufd =  Carbon::parse($cufdDataEvento->created_at)->addMinute(1) ->format('Y-m-d H:i');

                //si fecha inicio es menor que cufd error
                if ($fechaInicio < $fechaCufd)
                {
                    throw new \Exception("La fechas debe ser mayor a  {$fechaCufd}");
                }

            }

            $data = EventoSignificativo::create($dataValidated);
            if(in_array($data->evento->codigo_clasificador,[5,6,7])) {
                $this->registerEventSignificant($data);
            }
            $message = "Se registro correctamente!, sistema fuera de linea";
            DB::commit();
            return $this->CreatedResponse(new EventoSignificativoResource($data), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = 'Registro fallido!';
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function show(EventoSignificativo $eventoSignificativo)
    {
        try {
            $dataAdicional = $this->SuccessResponse('Registro recuperado correctamente!');
            return EventoSignificativoResource::make(
                $eventoSignificativo->load('facturas.venta','recepciones')
            )->additional($dataAdicional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obteción de datos fallida!', $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateEventoSignificativoRequest  $request
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEventoSignificativoRequest $request, EventoSignificativo $eventoSignificativo)
    {
        try {
            $dataValidated = $request->validated();

            if( in_array($eventoSignificativo->estado, ['RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("El evento ya fue {$eventoSignificativo->estado} y no puede ser editado.");

            }

            if( $request->has('fecha_fin') ) {
                $dataValidated['estado'] = 'FINALIZADO';
            }

            $eventoSignificativo->update($dataValidated);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse(new EventoSignificativoResource($eventoSignificativo), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EventoSignificativo  $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventoSignificativo $eventoSignificativo)
    {
        try {
            if( in_array($eventoSignificativo->estado, ['RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("El evento ya fue {$eventoSignificativo->estado} y no puede ser eliminado", 405);
            }

            $eventoSignificativo->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Eliminación fallida!', $error->getMessage(), $error->getCode());
        }
    }

    public function getSignificatEvent(EventoSignificativo $eventoSignificativo)
    {
//      dd($eventoSignificativo);
        try {
            $eventService = new EventoSignificativoService();
            $response = $eventService->getEventSignificat($eventoSignificativo);
            $message = 'Registro recuperado correctamente!';
            return $this->ResponseJson($response,$message);

//            return EventoSignificativoResource::make($eventoSignificativo->load('facturas.venta'))->additional($dataAdicional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obteción de datos fallida!', $error->getMessage(), Response::HTTP_BAD_REQUEST);
        }


    }


    public function registerEventSignificant(EventoSignificativo $eventoSignificativo)
    {
        DB::beginTransaction();
        try {

            if( in_array($eventoSignificativo->estado, ['RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("El evento significativo ya fue recepcionado", 405);
            }

            //si no hay facturas que enviar
            if($eventoSignificativo->facturas->isEmpty() && in_array($eventoSignificativo->evento->codigo_clasificador,[ 1, 2, 3, 4]) )
            {
                throw new \Exception("No hubo facturas que enviar, puede eliminar el evento!!");
            }
            /**
             * Registyro de nuyevo cufdd para la sucursal o punto venta
             */
            //cufd si evento es sucursal
            if($eventoSignificativo->sucursal_id && $eventoSignificativo->punto_venta_id)
            {
                $idCuis = $eventoSignificativo->sucursal->cuis->id;
            }
                //cufd si evento en pos
            if($eventoSignificativo->punto_venta_id)
            {
                $idCuis = $eventoSignificativo->pos->cuis->id;

            }

            //nuevo cufd cuando es eventos del 1,2,3,4
            if(in_array($eventoSignificativo->evento->codigo_clasificador,[ 1, 2, 3, 4,5,6,7])){
                $cufdService = new CufdService();
                $cufdService->handleStore(['cuis_id' => $idCuis]);
            }

            $eventService = new EventoSignificativoService();
            $eventService->registerEventSignificant($eventoSignificativo);
            DB::commit();
            return $this->CreatedResponse(new EventoSignificativoResource($eventoSignificativo), 'Se registro correctamente!', Response::HTTP_CREATED);
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
            return $this->ErrorResponse($message, $error, $statusCode);
        }


    }

    /**
     * Register invoices issued in contingency
     * @param \App\Http\Requests\StoreVentaBackRequest $ventaRequest
     * @param \App\Models\EventoSignificativo $eventoSignificativo
     */
    public function transcribe(StoreVentaRequest $requestVentaEmapa, StoreUpdateDetalleVentaRequest $requestDetalleVenta, StoreClienteRequest $requestCliente, EventoSignificativo $eventoSignificativo)
    {
        $dataVenta = $requestVentaEmapa->validated();
        $dataDetalleVenta = $requestDetalleVenta->validated();
        $dataCliente = $requestCliente->validated()['datosCliente'];

        $egresoMovimiento = 4;

        DB::beginTransaction();
        try {
            $date = Carbon::parse($dataVenta['fecha']);

            if ( ! $date->between($eventoSignificativo->fecha_inicio, $eventoSignificativo->fecha_fin) ) {
                throw new \Exception("La fecha de venta debe estar entre {$eventoSignificativo->fecha_inicio} y {$eventoSignificativo->fecha_fin}");
            }
            //secuencia
            $maxSecuencia = Venta::where('sucursal_id', $dataVenta['sucursal_id'])
                ->where('punto_venta_id', $dataVenta['punto_venta_id'])
                ->max('codigo_secuencia');
            $numeroSecuencial = $maxSecuencia + 1;
            $dataVenta['codigo_secuencia'] = $numeroSecuencial;


            $origen = "ventas";

            $idCliente = $dataVenta['cliente_id'];
            if($idCliente == 0)
            {
                $dataCliente['departamento_id']=1;
                $cliente = Cliente::create($dataCliente);
                $dataVenta['cliente_id'] = $cliente->id;
            }else{
                Cliente::where('id', $dataVenta['cliente_id'])
                    ->update(['razon_social' => $dataCliente['razon_social']]);
            }

            $sumaSubTotalValidacion = 0;
            foreach ($dataDetalleVenta['detalleVenta'] as $detalleVenta) {
                $subTotal = $detalleVenta['sub_total'];
                $sumaSubTotalValidacion += $subTotal;
            }
            $totalDescuento = ($sumaSubTotalValidacion*$dataVenta['descuento'])/100;
            $dataVenta['descuento'] = round($totalDescuento,2);
            /*Creacion de venta*/
            $venta = Venta::create($dataVenta);

            foreach ($dataDetalleVenta['detalleVenta'] as $detalleVenta) {
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

            $cufdDataEvento = Cufd::where('valor',$eventoSignificativo->cufd_evento)->first();
//            dd($cufdDataEvento->valor);
            $params = [
                'fecha' => $venta->fecha,
                'branchCode' => $venta->sucursal->codigo_siat,
                'posCode' => $venta->pos->codigo_siat,
                'codigoControl' => $cufdDataEvento->codigo_control,
                'departamento' => $venta->sucursal->departamento->departamento,
                'telefono' => $venta->sucursal->telefono,
                'direccion' => $venta->sucursal->direccion,
                'cufd' => $cufdDataEvento->valor,
                'cufdId' => $cufdDataEvento->id,
            ];

            $factura = $facturaService->createInvoice($venta->load(['inventarios.producto' => ['homologacion', 'atributos'],]), $params,$eventoSignificativo);

            $facturaService->emitOffline($factura, $eventoSignificativo->id);
            //facturas consumidas cafc
            $cafc = Cafc::where('cafc', $eventoSignificativo->cafc)->first();

            if ($cafc->numero_facturas_utilizadas >= $cafc->numero_fin) {
                throw new \Exception("Se ha alcanzado el límite de facturas para este código {$cafc->cafc} cafc");
            }
            $cafc->increment('numero_facturas_utilizadas',1);

            //send mail
            $guardar="si";
            $this->pdfFactura($factura->cuf,$guardar);

            if (!is_null($venta->cliente->email))
            {
                $this->sendMailInvoice($factura,$venta);
            }

            DB::commit();
            return $this->CreatedResponse($venta, 'Se registro correctamente', Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            $statusCode = Response::HTTP_PRECONDITION_FAILED;
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            if ($error->getCode()  == 503)
            {
                $statusCode = $error->getCode();
            }
            return $this->ErrorResponse($message, $error, $statusCode);
        }
    }

    public function sendMailInvoice($factura,$venta)
    {
        Mail::to($venta->cliente->email)->send(new SendInvoice($factura,$venta));
    }
    public function pdfFactura($cuf,$guardar = null)
    {
        $dataFactura = Factura::has('eventosSignificativos')->where('cuf',$cuf)->first();
        $leyendaRepresentacionGrafica = "";
        if (is_null($dataFactura))
        {
            $leyendaRepresentacionGrafica = "Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en línea";
        } else{
            $leyendaRepresentacionGrafica="Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido fuera de línea, verifique su envío con su proveedor o en la página web www.impuestos.gob.bo";

        }
        //si es offline enviamos una leyenda adicional
        $tipo = request('tipo','pagina');
        $usuario = auth()->user();
        $tipoImpresion = $usuario->tipoImpresion->first()['tipo'];

        $dataSistema = AutorizacionSistema::first();
        $dataFacturaVentaSucursalPos = Factura::with('venta.sucursal','venta.pos')->where('cuf',$cuf)->first();

        $dataVenta = Venta::with('inventarios.producto','inventarios.producto.unidadMedida.valorCatalogo')->where('id',$dataFacturaVentaSucursalPos['venta_id'])->first();
        $dataCliente = Venta::with('cliente')->where('id',$dataFacturaVentaSucursalPos['venta_id'])->first();
        $valorImpresionSiat = 0;
        if($tipoImpresion == 'rollo' || $tipo == 'rollo')
        {
            $valorImpresionSiat = 1;
        }else{
            $valorImpresionSiat = 2;
        }
        $urlSin = env('SIN_URL');
//        $qr = "{$urlSin}consulta/QR?nit=".$dataSistema->nit."&cuf=".$cuf."&numero=".$dataVenta['codigo_secuencia']."&t=".$valorImpresionSiat;
        $qr = "https://siat.impuestos.gob.bo/consulta/QR?nit=".$dataSistema->nit."&cuf=".$cuf."&numero=".$dataVenta['codigo_secuencia']."&t=".$valorImpresionSiat;

        if (!file_exists(public_path('qrcode'))) {
            mkdir(public_path('qrcode'));
        }
        QrCode::generate($qr,public_path('qrcode/'.$cuf.'.svg'));

        $formatter = new NumeroALetras();
        $numeroLiteral = $formatter->toInvoice($dataVenta->total,2,'BOLIVIANOS');
        $patron = "CON";
        // Reemplazar la palabra "CON" por una cadena vacía
        $texto_resultante = str_replace($patron, "", $numeroLiteral);
        // Eliminar cualquier espacio en blanco al final del texto resultante
        $numeroLiteral = trim($texto_resultante);

        if ($tipo == "rollo" || $tipoImpresion == "rollo")
        {
            $html = view('pdfFacturaRollo',
                compact('dataSistema','dataFacturaVentaSucursalPos','dataVenta','cuf','dataCliente','numeroLiteral','leyendaRepresentacionGrafica'));

            $pdf = PDF::setPaper(array( 0 , 0 , 226.77 , 120 ));

            $pdf->loadHTML($html)->render();
            $paginas = $pdf->getCanvas()->get_page_count();
            $espaciosEntrePaginas = $paginas-1; //hay un cierto espacio entre paginas que con 0.5cm aqui vemos cuantos espacios existe
            $totalEspacioEntrePaginas = $espaciosEntrePaginas*14.17; //aqui multiplicacmos los numero de espcios por 0.5 expresadno en pt
            $totalAlturaPaginaRollo = (120*$paginas)-$totalEspacioEntrePaginas;//aqui quitamos los espacios que se generan por esos 0.5 cm
            unset($pdf);

            $pdf = PDF::loadView('pdfFacturaRollo',
                compact('dataSistema','dataFacturaVentaSucursalPos','dataVenta','cuf','dataCliente','numeroLiteral','leyendaRepresentacionGrafica'))
                ->setPaper(array( 0 , 0 , 226.77 , $totalAlturaPaginaRollo));
        }else{
            $pdf = PDF::loadView('pdfFacturaPagina',compact('dataSistema','dataFacturaVentaSucursalPos','dataVenta','cuf','dataCliente','numeroLiteral','leyendaRepresentacionGrafica'))
                ->setPaper('letter');
        }

        if ($guardar == "si"){
            Storage::disk('public')->put('pdf/'.$cuf.'.pdf', $pdf->download()->getOriginalContent());
        }else{
            return $pdf->stream($cuf.'.pdf');
        }
    }



    /**
     * Register the specified resource in SIAT
     *
     * @param \App\Models\EventoSignificativo $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function register(EventoSignificativo $eventoSignificativo) {

        try {
            if( $eventoSignificativo->estado == 'INICIADO' ) {
                throw new \Exception("El evento debe estar finalizado", 405);
            }

            if( in_array($eventoSignificativo->estado, ['RECEPCIONADO', 'VALIDADO']) ) {
                throw new \Exception("El evento significativo ya fue recepcionado", 405);
            }

            $eventService = new EventoSignificativoService();

            $eventService->register($eventoSignificativo);

            return $this->CreatedResponse(new EventoSignificativoResource($eventoSignificativo), 'Se registro correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Registro de evento fallido!', $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Validate the specified resource in SIAT
     *
     * @param \App\Models\EventoSignificativo $eventoSignificativo
     * @return \Illuminate\Http\Response
     */
    public function validateReception(EventoSignificativo $eventoSignificativo) {
        try {
            if( in_array($eventoSignificativo->estado, ['INICIADO', 'FINALIZADO']) ) {
                throw new \Exception("El evento debe estar recepcionado", 405);
            }
            $eventService = new EventoSignificativoService();
            $response = $eventService->validate($eventoSignificativo);
            return $this->CreatedResponse($response, 'Se valido correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
//            throw $error;
            $message = "Registro fallido";
            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
