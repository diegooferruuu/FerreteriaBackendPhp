<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartamentoRequest;
use App\Http\Requests\UpdateDepartamentoRequest;
use App\Http\Resources\DepartamentoResource;
use App\Http\Traits\ApiResponser;
use App\Models\Departamento;
use Illuminate\Http\Response;

class DepartamentoController extends Controller
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
        return DepartamentoResource::collection(Departamento::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDepartamentoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDepartamentoRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = Departamento::create($dataValidate);
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
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Http\Response
     */
    public function show(Departamento $departamento)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return DepartamentoResource::make( $departamento->load(['regional', 'provincias']) )->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDepartamentoRequest  $request
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepartamentoRequest $request, Departamento $departamento)
    {
        try {
            $dataValidate = $request->validated();
            $departamento->update($dataValidate);
            // $data = Atributo::find($atributo->id);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($departamento, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Departamento  $departamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Departamento $departamento)
    {
        try {
            $departamento->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
