<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonaResource;
use App\Models\Persona;
use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdatePersonaRequest;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Response;

class PersonaController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //        $limit = $request->query('limit', $default = 10);
        //        try {
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return PersonaResource::collection(Persona::paginate(10))->additional($dataAdditional);

        //        } catch (\Throwable $error) {
        //            $message = 'Obteción de datos fallida.';
        //            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        //        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePersonaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePersonaRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = Persona::create($dataValidate);
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
     * @param  \App\Models\Persona  $personas
     * @return \Illuminate\Http\Response
     */
    public function show(Persona $persona)
    {

        //        try {
        $message = "Registro recuperado correctamente!!";
        $dataAdicional = $this->SuccessResponse($message);
        return PersonaResource::make($persona)->additional($dataAdicional);
        //
        //        } catch (\Throwable $error) {
        //            $message = 'Obteción de datos fallida.';
        //            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        //        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePersonaRequest  $request
     * @param  \App\Models\Persona  $persona
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePersonaRequest $request, Persona $persona)
    {
        try {
            $dataValidate = $request->validated();
            $persona->update($dataValidate);
            $data = Persona::find($persona->id_persona);
            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Persona  $persona
     * @return \Illuminate\Http\Response
     */
    public function destroy(Persona $persona)
    {
        try {
            $persona->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
