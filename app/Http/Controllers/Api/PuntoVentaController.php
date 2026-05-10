<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePuntoVentaRequest;
use App\Http\Requests\UpdatePuntoVentaRequest;
use App\Http\Resources\InventarioProductoResource;
use App\Http\Resources\InventarioProductoVentaMayorResource;
use App\Http\Resources\InventarioProductoVentaResource;
use App\Http\Resources\PuntoVentaResource;
use App\Http\Services\CufdService;
use App\Http\Services\CuisService;
use App\Http\Services\PuntoVentaService;
use App\Http\Traits\ApiResponser;
use App\Models\Inventario;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PuntoVentaController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('permission:listar-punto-venta')->only('index');
        $this->middleware(['permission:vista-punto-venta', 'permission:crear-punto-venta'])->only('store');
        $this->middleware(['permission:vista-punto-venta', 'permission:editar-punto-venta'])->only('update');
        $this->middleware( 'permission:ver-punto-venta')->only( 'show');
        $this->middleware(['permission:vista-punto-venta', 'permission:eliminar-punto-venta'])->only('destroy');

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $dataAdditional = $this->SuccessResponse('Registros recuperado correctamente!');
        return PuntoVentaResource::collection(
            PuntoVenta::with('tipo')-> when(request()->has('show_for') && request('show_for') == 'siat', function ($query) {
                $query->with('cuis.cufd')->withCount('facturasPendientesRecepcion');
            })
            ->filter()
            ->paginate($perPage)
        )->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePuntoVentaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePuntoVentaRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();
            //si no tenemos el codigo sit, vendra estado_codigo_siat false y creara registro un nuevo punto de venta en SIAT
            if($request->estado_codigo_siat == false)
            {
                // registro para SIAT
                $posService = new PuntoVentaService();
                $response = $posService->register($dataValidated);
                $dataValidated['codigo_siat'] = $response->RespuestaRegistroPuntoVenta->codigoPuntoVenta;
            }

            //creamos directamente si tenemos el codigo estado en true
            $data = PuntoVenta::create($dataValidated);

            $message = "Se registro correctamente!";

            // Para SIAT
             try {
                $cuisService = new CuisService();
                $cuis = $cuisService->handleStore(['punto_venta_id' => $data->id]);
            } catch (\Throwable $th) {
                $message .= " Error CUIS: " . $th->getMessage();
            }
            // crear cufd
            if( isset($cuis) ) {
                try {
                    $cufdService = new CufdService();
                    $cufdService->handleStore(['cuis_id' => $cuis->id]);
                } catch (\Throwable $th) {
                    $message .= " Error CUFD: " . $th->getMessage();
                }
            }
            DB::commit();
            return $this->CreatedResponse(new PuntoVentaResource($data), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            throw $error;
            $message = "Registro fallido!";
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PuntoVenta  $puntoVenta
     * @return \Illuminate\Http\Response
     */
    public function show(PuntoVenta $puntoVenta)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return PuntoVentaResource::make( $puntoVenta->load('tipo', 'sucursal', 'cuis.cufd', 'eventoSignificativo') )->additional((array) $dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePuntoVentaRequest  $request
     * @param  \App\Models\PuntoVenta  $puntoVenta
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePuntoVentaRequest $request, PuntoVenta $puntoVenta)
    {
        try {
            $dataValidate = $request->validated();
            $puntoVenta->update($dataValidate);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse(new PuntoVentaResource($puntoVenta), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PuntoVenta  $puntoVenta
     * @return \Illuminate\Http\Response
     */
    public function destroy(PuntoVenta $puntoVenta)
    {

        try {
            //? Analizar la eliminacion como cierre de punto de venta(SIAT)
            $puntoVenta->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Display a listing of the registered in SIAT
     *
     * @return \Illuminate\Http\Response
     */
    public function registered(Sucursal $sucursal)
    {
        try {
            $posService = new PuntoVentaService();
            $response = $posService->getRegistered($sucursal);
            $dataAdditional = $this->SuccessResponse('Registros recuperados correctamente!');

            return collect($response)->union($dataAdditional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Restore points of sale registered in SIAT
     * @param \App\Models\Sucursal $sucursal
     * @return \Illuminate\Http\Response
     */
    public function restore(Sucursal $sucursal) {
        DB::beginTransaction();
        try {
            $posService = new PuntoVentaService();
            $response = $posService->getRegistered($sucursal);
            $posSiat = collect($response['data']);
            $duplicateNamesPos = $posSiat->duplicates('nombrePuntoVenta')->all();

            $posSiat = $posSiat->map(function ($item, $key) use ($duplicateNamesPos) {
                if( array_key_exists($key, $duplicateNamesPos) ) {
                    $item->nombrePuntoVenta .= $item->codigoPuntoVenta;
                }
                return $item;
            });

            $tiposPuntosVenta = ValorCatalogo::catalogo(13)->pluck('id', 'descripcion');

            if( count($tiposPuntosVenta) == 0 ) {
                throw new \Exception("Sincroniza el catalogo tipo de punto de venta.");
            }

            foreach ($posSiat as $pos) {

                PuntoVenta::updateOrCreate([
                    'sucursal_id' => $sucursal->id,
                    'codigo_siat' => $pos->codigoPuntoVenta,
                ], [
                    'tipo_punto_venta_id' => $tiposPuntosVenta[$pos->tipoPuntoVenta],
                    'nombre' => $pos->nombrePuntoVenta,
                ]);
            }

            DB::commit();
            return $this->SuccessResponse('Restauracion de puntos de venta corectamente!');
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function buscarProductosVenta(Request $request, Sucursal $sucursal)
    {
        $search = $request->query('search',null);
        $codigoBarra = $request->query('codigoBarra',"false");
        $precioMayor = $request->query('precioMayor', "false");

        try {
            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            if($codigoBarra === "true")
            {
                return InventarioProductoVentaResource::collection(
                    Inventario::whereHas('producto',function (Builder $query) use  ($search) {
                        $query->where('codigo_barra', $search);
                    })->whereHas('producto.clasificacionProducto',function ($query){
                        $query->where('id',2);
                    })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->paginate(100)
                )->additional($dataAdditional);
            }

            if($precioMayor === "true")
            {
                return InventarioProductoVentaMayorResource::collection(
                    Inventario::whereHas('producto',function (Builder $query) use  ($search) {
                        $query->whereLike(['descripcion'], $search);
                        $query->whereHas('precioGeneral', function (Builder $query) {
                            $query->where('precio_mayor', '>', 0);
                        });
//                        $query->orderBy('id','ASC');
                    })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->orderBy('producto_id','ASC')->get()
                )->additional($dataAdditional);

            }
            if($precioMayor === "false"){

//                $query = Inventario::whereHas('producto',function (Builder $query) use  ($search) {
//                    $query->orderBy('id', 'ASC')->whereLike(['descripcion'], $search);
//                    $query->whereHas('precioGeneral', function (Builder $query) {
//                        $query->where('precio_menor', '>', 0);
//                    });
//
//                })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->get();
//                $query =   Inventario::whereHas('producto',function (Builder $query) use  ($search) {
////                    $query->whereLike(['descripcion'], $search);
//                    $query->orderBy('descripcion', 'ASC');
////                    $query->whereHas('precioGeneral', function (Builder $query) {
////                        $query->where('precio_menor', '>', 0);
////                    });
//                })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->get();
////                dd(json_decode($query));
//                return InventarioProductoVentaResource::collection(  $query   )->additional($dataAdditional);
//               $query= Inventario::with(['producto' => function($query) use ($search) {
//                    $query->whereLike(['descripcion'], $search);
//                    $query->whereHas('precioGeneral', function (Builder $query) {
//                        $query->where('precio_menor', '>', 0);
//                    });
//                    $query->orderBy('descripcion', 'ASC');
//                }])->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->get();
//                dd(json_decode($query));



                return InventarioProductoVentaResource::collection(
                    Inventario::whereHas('producto',function (Builder $query) use  ($search) {
                        $query->whereLike(['descripcion'], $search);
                        $query->whereHas('precioGeneral', function (Builder $query) {
                            $query->where('precio_menor', '>', 0);
                        });
                    })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->orderBy('producto_id', 'ASC')->get()
                )->additional($dataAdditional);
            }

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }


    }
}
