<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovimientoRequest;
use App\Http\Requests\UpdateMovimientoRequest;
use App\Http\Resources\MovimientoResource;
use App\Http\Traits\ApiResponser;
use App\Models\Movimiento;
use Illuminate\Http\Response;

class MovimientoController extends Controller
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
        return MovimientoResource::collection(Movimiento::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreMovimientoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMovimientoRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = Movimiento::create($dataValidate);
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
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Http\Response
     */
    public function show(Movimiento $movimiento)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return MovimientoResource::make($movimiento)->additional($dataAdicional);
    
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateMovimientoRequest  $request
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMovimientoRequest $request, Movimiento $movimientoEdit)
    {
        try {
            $dataValidate = $request->validated();
            $movimientoEdit->update($dataValidate);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($movimientoEdit, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Movimiento  $movimiento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Movimiento $movimiento)
    {
        try {
            $movimiento->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
