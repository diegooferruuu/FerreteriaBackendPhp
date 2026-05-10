<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnulacionFacturaRequest;
use App\Http\Requests\UpdateAnulacionFacturaRequest;
use App\Http\Resources\AnulacionFacturaResource;
use App\Http\Services\FacturaService;
use App\Http\Traits\ApiResponser;
use App\Mail\SendMailAnnularInvoice;
use App\Models\AnulacionFactura;
use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AnulacionFacturaController extends Controller
{
    use ApiResponser;
    public function __construct()
    {
        $this->middleware('permission:listar-venta')->only('index','show');
        $this->middleware('permission:eliminar-venta')->only('store');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return AnulacionFacturaResource::collection(AnulacionFactura::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAnulacionFacturaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnulacionFacturaRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();
//            dd($dataValidated);
            $fechaVenta = Factura::with('venta')->where('id',$dataValidated['factura_id'])->first()->venta['fecha'];
            $fechaActual = Carbon::now()->format('Y-m-d');
            $dataVencimientoFactura = Carbon::parse($fechaVenta)->addMonth()->format('Y-m-10');

            if($fechaActual > $dataVencimientoFactura)
            {
                throw new \Exception("No puede anular la factura, fuera de periodo !!");
            }


//            $dataFactura = Factura::with('venta.cliente')->find($dataValidated['factura_id']);
//            dd($dataFactura->venta->cliente->email);

            $facturaService = new FacturaService();
            $anulacionFactura = $facturaService->anulateInvoice($dataValidated);

            if($anulacionFactura->codigo_estado == 905)
            {
                $dataFactura = Factura::with('venta.cliente')->find($dataValidated['factura_id']);
                $this->sendMailInvoiceAnnularInvoice($dataFactura);
            }

            DB::commit();
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($anulacionFactura, $message, Response::HTTP_CREATED);
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
    public function sendMailInvoiceAnnularInvoice($dataFactura)
    {
        Mail::to($dataFactura->venta->cliente->email)->send(new SendMailAnnularInvoice($dataFactura));
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AnulacionFactura  $anulacionFactura
     * @return \Illuminate\Http\Response
     */
    public function show(AnulacionFactura $anulacionFactura)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return AnulacionFacturaResource::make($anulacionFactura)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }
}
