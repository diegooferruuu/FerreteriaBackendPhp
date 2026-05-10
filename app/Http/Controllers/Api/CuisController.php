<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCuisRequest;
use App\Http\Requests\UpdateCuisRequest;
use App\Http\Resources\CuisResource;
use App\Http\Services\CuisService;
use App\Http\Services\Siat\CodeObtaining;
use App\Http\Traits\ApiResponser;
use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Http\Response;

class CuisController extends Controller
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
        return CuisResource::collection(Cuis::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCuisRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCuisRequest $request)
    {
       $data  = $request->validated();
        try {
            $cuisService = new CuisService();
            $data = $cuisService->handleStore($data);

            //para solicitar cuis para el punto de venta aunque sera el mismo cuis
            $sucursal = Sucursal::where('id',$data['sucursal_id'])->first();
            if($sucursal->codigo_siat == 0)
            {
                $pos = PuntoVenta::where('codigo_siat',0)->where('sucursal_id',$sucursal->id)->first();
                $cuisService->handleStore(['punto_venta_id' => $pos->id]);
            }
            return $this->CreatedResponse(new CuisResource($data), 'Se registro correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cuis  $cuis
     * @return \Illuminate\Http\Response
     */
    public function show(Cuis $cuis)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return CuisResource::make($cuis)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCuisRequest  $request
     * @param  \App\Models\Cuis  $cuis
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCuisRequest $request, Cuis $cuis)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cuis  $cuis
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cuis $cuis)
    {
        //
    }
}
