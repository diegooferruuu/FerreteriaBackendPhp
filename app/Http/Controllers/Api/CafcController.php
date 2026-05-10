<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateCafcRequest;
use App\Http\Resources\CafcResource;
use App\Http\Traits\ApiResponser;
use App\Models\Cafc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CafcController extends Controller
{
    use ApiResponser;
    public function __construct()
    {
        $this->middleware('role:administrador');

    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request('per_page', 20);
        $estadoCafc = request('estado_cafc', null);
        try {
            $message = "Registros recuperados correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            return CafcResource::collection(
                Cafc::when($estadoCafc, function ($query, $estado) {
                    return $query->where('estado', $estado);
                })->orderBy('id')->paginate($perPage)
            )->additional($dataAdditional);

        } catch (\Throwable $error) {
            $message = "Registro no recuperados!!";
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateCafcRequest $request)
    {
        $dataCafc = $request->validated();
        try {
            $cafc = Cafc::create($dataCafc);

            $message = "Se registro correctamente!!";
            return  $this->CreatedResponse($cafc, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {

            $message = "Registro fallido";
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cafc $cafc)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return CafcResource::make($cafc)->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateCafcRequest $request, Cafc $cafc)
    {
        $dataCafc = $request->validated();

        try {
            $cafc->update($dataCafc);
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($cafc, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cafc $cafc)
    {
        try {

            if ($cafc->estado == 'VALIDO') {
                $cafc->update(['estado' => 'INVALIDO']);
                $message = "Cafc Nro. ".$cafc->cafc." invalidado correctamente!!";
            }else if($cafc->estado == 'INVALIDO'){
                $cafc->update(['estado' => 'VALIDO']);
                $message = "Cafc Nro. ".$cafc->cafc." validado correctamente!!";
            }
            return $this->SuccessResponse($message,Response::HTTP_NO_CONTENT);

        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
