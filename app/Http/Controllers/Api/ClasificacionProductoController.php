<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClasificacionProductoRequest;
use App\Http\Requests\UpdateClasificacionProductoRequest;
use App\Http\Resources\ClasificacionProductoResource;
use App\Http\Traits\ApiResponser;
use App\Models\ClasificacionProducto;
use Illuminate\Http\Response;

class ClasificacionProductoController extends Controller
{
    use ApiResponser;
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
        return ClasificacionProductoResource::collection(ClasificacionProducto::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClasificacionProductoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClasificacionProductoRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = ClasificacionProducto::create($dataValidate);
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
     * @param  \App\Models\ClasificacionProducto  $clasificacionProducto
     * @return \Illuminate\Http\Response
     */
    public function show(ClasificacionProducto $clasificacionProducto)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return ClasificacionProductoResource::make($clasificacionProducto)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClasificacionProductoRequest  $request
     * @param  \App\Models\ClasificacionProducto  $clasificacionProducto
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClasificacionProductoRequest $request, ClasificacionProducto $clasificacionProducto)
    {
        try {
            $dataValidate = $request->validated();
            $clasificacionProducto->update($dataValidate);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($clasificacionProducto, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ClasificacionProducto  $clasificacionProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClasificacionProducto $clasificacionProducto)
    {
        try {
            $clasificacionProducto->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
