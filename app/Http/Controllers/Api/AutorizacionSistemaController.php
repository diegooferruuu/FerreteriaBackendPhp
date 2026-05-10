<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAutorizacionSistemaRequest;
use App\Http\Requests\UpdateAutorizacionSistemaRequest;
use App\Http\Resources\AutorizacionSistemaResource;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\FileUpload;
use App\Models\Autorizacion;
use App\Models\AutorizacionSistema;
use Illuminate\Http\Response;

class AutorizacionSistemaController extends Controller
{
    use ApiResponser, FileUpload;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $dataAdditional = $this->SuccessResponse('Registros recuperado correctamente!');
        return AutorizacionSistemaResource::collection(AutorizacionSistema::get())->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAutorizacionSistemaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAutorizacionSistemaRequest $request)
    {
        try {
            $dataValidated = $request->validated();
            $data = AutorizacionSistema::create($dataValidated);
            return $this->CreatedResponse(new AutorizacionSistemaResource($data), "Se registro correctamente!", Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            return $this->ErrorResponse("Registro fallido!", $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AutorizacionSistema  $autorizacionSistema
     * @return \Illuminate\Http\Response
     */
    public function show(AutorizacionSistema $autorizacionSistema)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return AutorizacionSistemaResource::make($autorizacionSistema)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAutorizacionSistemaRequest  $request
     * @param  \App\Models\AutorizacionSistema  $autorizacionSistema
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAutorizacionSistemaRequest $request, AutorizacionSistema $autorizacionSistema)
    {
        try {
            $dataValidated = $request->validated();
            if (isset($dataValidated['logo'])) {
                try {
                    $relativePath = $this->insertBase64Image($dataValidated['logo'], 'logotipo');
                } catch (\Throwable $th) {
                    $relativePath = null;
                }
                $dataValidated['logo'] = $relativePath;
            }
            $autorizacionSistema->update($dataValidated);
            // $data = Atributo::find($atributo->id);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($autorizacionSistema, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AutorizacionSistema  $autorizacionSistema
     * @return \Illuminate\Http\Response
     */
    public function destroy(AutorizacionSistema $autorizacionSistema)
    {
        try {
            $autorizacionSistema->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
