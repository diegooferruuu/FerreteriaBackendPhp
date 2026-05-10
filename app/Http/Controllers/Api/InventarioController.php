<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventarioRequest;
use App\Http\Requests\UpdateInventarioRequest;
use App\Http\Resources\InventarioProductoResource;
use App\Http\Resources\InventarioResource;
use App\Http\Traits\ApiResponser;
use App\Models\Inventario;
use App\Models\PrecioParticular;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;

            $resumenInventario = DB::table('inventario_movimiento')->select(
                'inventario_id',
		        DB::raw("COALESCE(SUM(inventario_movimiento.inicial), 0) AS total_inventario_inicial"),
		        DB::raw("COALESCE(SUM(inventario_movimiento.ingresos), 0) AS total_ingresos"),
		        DB::raw("COALESCE(SUM(inventario_movimiento.egresos), 0) AS total_egresos"),
                DB::raw('AVG(precio) AS costo_ingresos'),
                DB::raw('AVG(precio) AS costo_egresos')
            )
            ->leftJoin('movimientos', 'movimientos.id', 'inventario_movimiento.movimiento_id')
            ->groupBy('inventario_id');


            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            return InventarioResource::collection(
                Inventario::when( request('sucursal_id') && is_numeric( request('sucursal_id') ), function($query) {
                    $query->where("sucursal_id", request('sucursal_id') );
                })
                ->leftJoinSub($resumenInventario, 'resumen_inventario', function ($join) {
                    $join->on('inventario.id', 'resumen_inventario.inventario_id');
                })
                ->filter()
                ->paginate($perPage)
            )->additional($dataAdditional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInventarioRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventarioRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = Inventario::create($dataValidate);
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function show(Inventario $inventario)
    {
        try {

            $resumenInventario = DB::table('inventario_movimiento')->select(
                'inventario_id',
                DB::raw("COALESCE(SUM(inventario_movimiento.inicial), 0) AS total_inventario_inicial"),
                DB::raw("COALESCE(SUM(inventario_movimiento.ingresos), 0) AS total_ingresos"),
		        DB::raw("COALESCE(SUM(inventario_movimiento.egresos), 0) AS total_egresos"),
                DB::raw('AVG(precio) AS costo_ingresos'),
                DB::raw('AVG(precio) AS costo_egresos')
            )
            ->leftJoin('movimientos', 'movimientos.id', 'inventario_movimiento.movimiento_id')
            ->where('inventario_id', $inventario->id)
            ->groupBy('inventario_id');

            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return InventarioResource::make(
                Inventario::leftJoinSub($resumenInventario, 'resumen_inventario', function($join) {
                    $join->on('inventario.id', 'resumen_inventario.inventario_id');
                })
                ->find($inventario->id)
            )->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventarioRequest  $request
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventarioRequest $request, Inventario $inventario)
    {
        try {
            $dataValidate = $request->validated();
            $inventario->update($dataValidate);
            // $data = Atributo::find($atributo->id);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($inventario, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\Response
     */
    public function destroy(Inventario $inventario)
    {
        try {
            $inventario->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }

    public function buscarProductos(Sucursal $sucursal, Request $request)
    {
        $search = $request->query('search',null);
        $idProveedor = $request->query('idProveedor',null);

        try {
            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            if($idProveedor != null)
            {
                return InventarioProductoResource::collection(Inventario::whereHas('producto',function (Builder $query) use  ($search,$idProveedor) {
                    $query->whereLike(['id','descripcion'], $search);
                    $query->where('proveedor_id',$idProveedor);
                })->where('sucursal_id',$sucursal->id)->paginate(20))->additional($dataAdditional);
            }
            return InventarioProductoResource::collection(Inventario::whereHas('producto',function (Builder $query) use  ($search,$idProveedor) {
                $query->whereLike(['id','descripcion'], $search);
            })->where('sucursal_id',$sucursal->id)->where('cantidad','>',0)->paginate(20))->additional($dataAdditional);


        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }

    }
}
