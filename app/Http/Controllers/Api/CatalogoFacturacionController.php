<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogoFacturacionRequest;
use App\Http\Requests\UpdateCatalogoFacturacionRequest;
use App\Http\Resources\CatalogoFacturacionResource;
use App\Http\Traits\ApiResponser;
use App\Models\CatalogoFacturacion;
use Illuminate\Http\Response;

class CatalogoFacturacionController extends Controller
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
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctamente!');
        return CatalogoFacturacionResource::collection(CatalogoFacturacion::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCatalogoFacturacionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCatalogoFacturacionRequest $request)
    {
        try {
            $dataValidated = $request->validated();
            $data = CatalogoFacturacion::create($dataValidated);

            return $this->CreatedResponse(new CatalogoFacturacionResource($data), 'Se registro correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Registro fallido', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CatalogoFacturacion  $catalogoFacturacion
     * @return \Illuminate\Http\Response
     */
    public function show(CatalogoFacturacion $catalogoFacturacion)
    {
        try {
            $dataAdditional = $this->SuccessResponse('Registro recuperado correctamente!');
            return CatalogoFacturacionResource::make($catalogoFacturacion)->additional($dataAdditional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obtención de datos fallida!', $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCatalogoFacturacionRequest  $request
     * @param  \App\Models\CatalogoFacturacion  $catalogoFacturacion
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCatalogoFacturacionRequest $request, CatalogoFacturacion $catalogoFacturacion)
    {
        try {
            $dataValidated = $request->validated();
            $catalogoFacturacion->update($dataValidated);
            return $this->CreatedResponse(new CatalogoFacturacionResource($catalogoFacturacion), 'Se editó correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Edición fallida', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CatalogoFacturacion  $catalogoFacturacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(CatalogoFacturacion $catalogoFacturacion)
    {
        try {
            $catalogoFacturacion->delete();
            return $this->SuccessResponse('Se eliminó correctamente!');
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Eliminación fallida!', $error, Response::HTTP_NO_CONTENT);
        }
    }
}
