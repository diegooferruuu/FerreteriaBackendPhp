<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventarioMovimientoRequest;
use App\Http\Requests\UpdateInventarioMovimientoRequest;
use App\Http\Resources\InventarioMovimientoResource;
use App\Http\Traits\ApiResponser;
use App\Models\InventarioMovimiento;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventarioMovimientoController extends Controller
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

            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            return InventarioMovimientoResource::collection(
                InventarioMovimiento::select(
                    '*',
                    DB::raw("sum( ( coalesce(inicial, 0) + coalesce(ingresos, 0) ) - coalesce(egresos, 0) ) over(PARTITION BY inventario_id ORDER BY fecha ASC, id) as saldo")
                )
                ->when( request('inventario') && is_numeric( request('inventario') ), function($query) {
                    $query->where("inventario_id", request('inventario') );
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
     * @param  \App\Http\Requests\StoreInventarioMovimientoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventarioMovimientoRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = InventarioMovimiento::create($dataValidate);
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
     * @param  \App\Models\InventarioMovimiento  $inventarioMovimiento
     * @return \Illuminate\Http\Response
     */
    public function show(InventarioMovimiento $inventarioMovimiento)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return InventarioMovimientoResource::make($inventarioMovimiento)->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInventarioMovimientoRequest  $request
     * @param  \App\Models\InventarioMovimiento  $inventarioMovimiento
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInventarioMovimientoRequest $request, InventarioMovimiento $inventarioMovimiento)
    {
        try {
            $dataValidate = $request->validated();
            $inventarioMovimiento->update($dataValidate);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($inventarioMovimiento, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InventarioMovimiento  $inventarioMovimiento
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventarioMovimiento $inventarioMovimiento)
    {
        try {
            $inventarioMovimiento->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
