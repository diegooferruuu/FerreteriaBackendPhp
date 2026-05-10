<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportPrecioGeneralRequest;
use App\Http\Requests\StorePrecioGeneralRequest;
use App\Http\Requests\UpdatePrecioGeneralRequest;
use App\Http\Resources\PrecioGeneralResource;
use App\Http\Traits\ApiResponser;
use App\Imports\PreciosGeneralesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PrecioGeneral;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PrecioGeneralController extends Controller
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
        return PrecioGeneralResource::collection(
            PrecioGeneral::filter()->paginate($perPage)
        )->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePrecioGeneralRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePrecioGeneralRequest $request)
    {
        try {
            $dataValidated = $request->validated();
            $data = PrecioGeneral::create($dataValidated);
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse(new PrecioGeneralResource($data), $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PrecioGeneral  $precioGeneral
     * @return \Illuminate\Http\Response
     */
    public function show(PrecioGeneral $precioGeneral)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return PrecioGeneralResource::make($precioGeneral)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePrecioGeneralRequest  $request
     * @param  \App\Models\PrecioGeneral  $precioGeneral
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePrecioGeneralRequest $request, PrecioGeneral $precioGeneral)
    {
        try {
            $dataValidated = $request->validated();
            $precioGeneral->update($dataValidated);
            
            return $this->CreatedResponse($precioGeneral, 'Se edito correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Edicion fallida', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PrecioGeneral  $precioGeneral
     * @return \Illuminate\Http\Response
     */
    public function destroy(PrecioGeneral $precioGeneral)
    {
        try {
            $precioGeneral->delete();

            return $this->SuccessResponse('Se eliminó correctamente!');
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Eliminación fallida!', $error, Response::HTTP_NO_CONTENT);
        }
    }
}
