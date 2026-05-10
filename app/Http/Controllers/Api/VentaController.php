<?php

namespace App\Http\Controllers\Api;

use App\Enums\EmissionCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\StoreUpdateDetalleVentaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Resources\VentaResource;
use App\Http\Services\Codigos\VerificacionNitService;
use App\Http\Services\FacturaService;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Siat\CompressFile;
use App\Http\Traits\Siat\Cuf;
use App\Http\Traits\Siat\Hasher;
use App\Http\Traits\Siat\XmlFile;
use App\Jobs\SendMailInvoiceJob;
use App\Mail\SendInvoice;
use App\Models\AutorizacionSistema;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Inventario;
use App\Models\InventarioMovimiento;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\TipoImpresion;
use App\Models\ValorCatalogo;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Validation\Rule;
use Mpdf\Mpdf;
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;

class VentaController extends Controller
{
    use ApiResponser, Cuf, XmlFile, CompressFile, Hasher;
    public function __construct()
    {
        $this->middleware('permission:listar-venta')->only('facturasEmitidas');
        $this->middleware('permission:crear-venta')->only('store');
        $this->middleware('permission:reportes')->only('export');

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //
    }
    //sucursal/3/pos/3
    public function facturasEmitidas(Sucursal $sucursal, PuntoVenta $puntoVenta)
    {
//        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $fechaInicio =Carbon::parse( request('fecha_inicio',Carbon::now()))->format('Y-m-d 00:00:00');
        $fechaFin = Carbon::parse(request('fecha_fin',Carbon::now()))->format('Y-m-d 23:59:59');
        $search = request('search',null);

        $message = "Registros recuperados correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return VentaResource::collection(Venta::whereHas('factura', function($query) use ($search) {
                $query->whereLike(['razon_social','numero_documento_identidad'], $search);
        })->orWhere(function ($query) use ($search) {
            if(is_numeric($search))
            {
                $query->where( 'codigo_secuencia',intval($search));
            }
        })
            ->where('sucursal_id',$sucursal->id)
            ->where('punto_venta_id',$puntoVenta->id)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('codigo_secuencia','ASC')->get())->additional($dataAdditional);
    }
    public function verificacionEstadoFactura(Request $request)
    {
        $data = $request->validate([
                'id'=> 'required',
                'sucursal_id' => 'required',
                'punto_venta_id' =>'required',
        ]);

        try {
            if($data['sucursal_id']) {
                $sucursal = Sucursal::findOrFail($data['sucursal_id']);

                if( !( $cufd = $sucursal->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para la sucursal {$sucursal->nombres}");
                }


            }
            if( $data['punto_venta_id'] ) {
                $pos = PuntoVenta ::findOrFail($data['punto_venta_id']);

                if( !( $cufd = $pos->cuis?->cufd ) ) {
                    throw new \Exception("Establece un CUFD para el punto de venta {$pos->nombre}");
                }
            }
            $facturaService = new FacturaService();
            $response = $facturaService->verifyInvoice($data);

            $message = "Se recepciono correctamente!";
            return $this->ResponseJson($response,$message);
        } catch (\Throwable $error) {

            return $this->ErrorResponse('Registro fallido!', $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, StoreVentaRequest $requestVenta, StoreUpdateDetalleVentaRequest $requestDetalleVenta, StoreClienteRequest $requestCliente )
    {
        $dataVenta = $requestVenta->validated();
        $dataDetalleVenta = $requestDetalleVenta->validated();
        $dataCliente = $requestCliente->validated()['datosCliente'];

        $egresoMovimiento = 4;

        DB::beginTransaction();
        try {
            //secuencia
//            ->lockForUpdate()
            $maxSecuencia = Venta::where('sucursal_id', $dataVenta['sucursal_id'])
                ->where('punto_venta_id', $dataVenta['punto_venta_id'])
                ->max('codigo_secuencia');
            $numeroSecuencial = $maxSecuencia + 1;
            $dataVenta['codigo_secuencia'] = $numeroSecuencial;

            //fecha
            $fechaRegistro = Carbon::now()->format('Y-m-d\TH:i:s.v');
            $dataVenta['fecha']=$fechaRegistro;

            $origen = "ventas";
//            dd(isset($dataCliente));
            //tarjeta
            if (isset($dataVenta['informacion_tarjeta']))
            {
//                obfuscate($venta->informacion_tarjeta, '0', 4, 4)
                $primerosDigitos = substr($dataVenta['informacion_tarjeta'], 0,4);
                $ultimosDigitos = substr($dataVenta['informacion_tarjeta'], -4,4);

                $dataVenta['informacion_tarjeta'] = $primerosDigitos.'00000000'.$ultimosDigitos;
            }
//            dd($dataVenta['informacion_tarjeta']);
            //registro ccliente
            $idCliente = $dataVenta['cliente_id'];
            if($idCliente == 0)
            {
                $cliente = $this->registroCliente($dataCliente,$dataVenta['sucursal_id']);
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

            /* registro de los itemsVentas*/
//            $sumaSubTotalValidacion = 0;
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

//            if($venta->total >= 1000 && $dataCliente['cedula_nit'] == '0')
//            {
//                $ventaTotal = number_format(round($venta->total,2),2,'.',',');
//                throw new \Exception("El monto es de Bs $ventaTotal debe añadir un cliente valido!!");
//            }

            if($venta->total >= 5 && $dataCliente['cedula_nit'] == '99003')
            {
                $ventaTotal = number_format(round($venta->total,2),2,'.',',');
                throw new \Exception("El monto es de Bs $ventaTotal debe añadir un cliente valido!!");
            }
            $facturaService = new FacturaService();

            $factura= $facturaService->register($venta);
            $cufFactura = $factura->cuf;
            $guardar="si";
            $this->pdfFactura($factura->cuf,$guardar);

            if (!is_null($venta->cliente->email))
            {
               $this->sendMailInvoice($factura,$venta);
            }
            if ($dataCliente['cedula_nit'] == '99001'){
                Cliente::where('cedula_nit', '99001')
                    ->update(['razon_social' => '']);
            }

            DB::commit();
            $message = "Se registro correctamente!!";
            return  $this->CreatedResponse($cufFactura, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollback();
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

            return $this->ErrorResponse($message, $error, $statusCode);
        }
    }

    public function sendMailInvoice($factura,$venta)
    {
        Mail::to($venta->cliente->email)->send(new SendInvoice($factura,$venta));
    }
    public function reenviarFacturaEmail(Request $request)
    {
        $data = $request->validate([
            'id_factura'=> 'required|exists:facturas,id',
        ]);
        try {
            $dataFactura = Factura::find($data['id_factura']);
            $existeArchivo = Storage::disk('public')->exists('pdf/'.$dataFactura->cuf.'.pdf');
            if(!$existeArchivo)
            {
                $guardar="si";
                $this->pdfFactura($dataFactura->cuf,$guardar);
            }
            $this->sendMailInvoice($dataFactura,$dataFactura->venta);

            $message = "Se envio correctamente!!";
            return  $this->CreatedResponse("Enviado correctamente", $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Envio erroneo!!";
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }
    public function validarCorreo($email)
    {
        list($username, $domain) = explode('@', $email);
        if (!checkdnsrr($domain, 'MX')) {
            return response()->json(['error' => 'El dominio del correo no existe'], 400);
        }
        return response()->json(['message' => 'Correo válido'], 200);
    }
    public function registroCliente($dataCliente,$idSucursal)
    {
        $dataCliente['departamento_id']=1;
        $codigoDocumentoId = ValorCatalogo::find($dataCliente['tipo_documento_id'])['codigo_clasificador'];

        $responseVerificacion = $this->verificacionComunicacionSiat();
        $codeStatus =$responseVerificacion->getStatusCode();
        $dataCliente['sucursal_id'] = $idSucursal;

//        if($codigoDocumentoId == '5' && $codeStatus == 200)
//        {
//            $verificacionNitService = new VerificacionNitService();
//            $verificacionNitService->handleStore($dataCliente);
//            $dataCliente['verificacion']=1;
//        }

        $cliente = Cliente::create($dataCliente);
        return $cliente;
    }
    public function pdfFactura($cuf,$guardar = null)
    {
//        $dataTipoImpresion = TipoImpresion::first();
//        $tipoImpresion = $dataTipoImpresion->tipo;
//        $tipoSiat = $dataTipoImpresion->tipo_siat;
//        $dataFactura = Factura::with ('eventosSignificativos')->where('cuf',$cuf)->where('estado','PENDIENTE')->first();
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
//            dd($pdf);
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

    public function export(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date'
        ]);

        $fechaInicio = Carbon::parse(request('fecha_inicio', Carbon::now()))->format('Y-m-d 00:00:00');
        $fechaFin = Carbon::parse(request('fecha_fin', Carbon::now()))->format('Y-m-d 23:59:59');
        try {

            $ventas = DB::table('detalle_venta')
                ->join('ventas', 'detalle_venta.venta_id', '=', 'ventas.id')
                ->join('inventario', 'detalle_venta.inventario_id', '=', 'inventario.id')
                ->join('productos', 'inventario.producto_id', '=', 'productos.id')
                ->join('procedencias', 'productos.procedencia_id', '=', 'procedencias.id')
                ->join('unidad_medidas', 'productos.unidad_medida_id', '=', 'unidad_medidas.id')
                ->join('valores_catalogo', 'unidad_medidas.valor_catalogo_id', '=', 'valores_catalogo.id')
                ->join('homologacion_productos', 'productos.id', '=', 'homologacion_productos.producto_id')

                ->select(
                    'productos.id',
                    DB::raw('SUM(detalle_venta.cantidad) as total_cantidad'),
                    'productos.producto',
                    'productos.descripcion',
                    'procedencias.procedencia',
                    'unidad_medidas.unidad_medida',
                    'valores_catalogo.codigo_clasificador',
                    'homologacion_productos.codigo_siat',
                    'detalle_venta.precio',
                    'detalle_venta.descuento',
                    DB::raw('(detalle_venta.precio - detalle_venta.descuento) as precio_decuento'),
                    DB::raw('(SUM(detalle_venta.cantidad) * (detalle_venta.precio - detalle_venta.descuento)) as total')
                )
                ->whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->where('ventas.estado', 'ACTIVO')
                ->groupBy('detalle_venta.precio',
                    'productos.id',
                    'productos.producto',
                    'productos.descripcion',
                    'procedencias.procedencia',
                    'unidad_medidas.unidad_medida',
                    'valores_catalogo.codigo_clasificador',
                    'homologacion_productos.codigo_siat',
                    'detalle_venta.precio',
                    'detalle_venta.descuento',
                )
                ->orderBy('productos.id','ASC')
                ->get();

	   $descuentoTotal = Venta::whereBetween('ventas.fecha', [$fechaInicio, $fechaFin])
                ->where('estado','ACTIVO')
                ->sum('descuento');
            $total = $ventas->sum('total');

            return Excel::download(new VentasExport($ventas, $fechaInicio, $fechaFin,$total,$descuentoTotal), 'ventas.xlsx');

            $message = "Exportación exitosa";
            return $this->ResponseJson([], $message);
        } catch (\Throwable $error) {

            $message = 'Error en la exportacion.';
            return $this->ErrorResponse($message, $error, Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

}
