<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreValorCatalogoRequest;
use App\Http\Requests\UpdateValorCatalogoRequest;
use App\Http\Resources\ValorCatalogoResource;
use App\Http\Traits\ApiResponser;
use App\Models\ValorCatalogo;
use Illuminate\Support\Facades\Storage;

class ValorCatalogoController extends Controller
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
        return ValorCatalogoResource::collection(ValorCatalogo::with('sincronizacion.catalogo')->filter()->get())->additional($dataAdditional);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateValorCatalogoRequest  $request
     * @param  \App\Models\ValorCatalogo  $valorCatalogo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateValorCatalogoRequest $request, ValorCatalogo $valorCatalogo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ValorCatalogo  $valorCatalogo
     * @return \Illuminate\Http\Response
     */
    public function destroy(ValorCatalogo $valorCatalogo)
    {
        //
    }
}
