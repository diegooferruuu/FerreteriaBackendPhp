<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Http\Resources\SucursalResource;
use App\Http\Services\CufdService;
use App\Http\Services\CuisService;
use App\Http\Traits\ApiResponser;
use App\Models\Cuis;
use App\Models\Departamento;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware(['permission:vista-gestion-sucursal','permission:listar-sucursal'])->only('index');
        $this->middleware(['permission:vista-gestion-sucursal','permission:crear-sucursal'])->only('store');
        $this->middleware(['permission:vista-gestion-sucursal','permission:editar-sucursal'])->only('update','show');
        $this->middleware(['permission:vista-gestion-sucursal','permission:eliminar-sucursal'])->only('destroy');
//        $this->middleware(['permission:vista-contactos','permission:ver-producto'])->only('show');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $message = "Registros recuperados correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);

        return SucursalResource::collection(
            Sucursal::when(request()->has('show_for') && request('show_for') == 'siat', function ($query) {
                $query->with(['cuis.cufd'])->withCount(['cuisPosCaducos', 'cufdPosCaducos', 'facturasPendientesRecepcion']);
            })
            ->filter()
            ->paginate($perPage)
        )->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSucursalRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSucursalRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidate = $request->validated();
            $data = Sucursal::create($dataValidate);
                // Para siat
            try {
                $cuisService = new CuisService();
                $cuis = $cuisService->handleStore(['sucursal_id' => $data->id]);
            } catch (\Throwable $th) {
                throw $th;
            }
                // crear cufd
                // Para SIAT
                /* try {
                    if( isset($cuis) ) {
                        $cufdService = new CufdService();
                        $cufdService->handleStore(['cuis_id' => $cuis->id]);
                    }
                } catch (\Throwable $th) {
                    throw $th;

                } */


            DB::commit();
            $message = "Se registro correctamente!.";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sucursal  $sucursal
     * @return \Illuminate\Http\Response
     */
    public function show(Sucursal $sucursal)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return SucursalResource::make($sucursal)->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSucursalRequest  $request
     * @param  \App\Models\Sucursal  $sucursal
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSucursalRequest $request, Sucursal $sucursal)
    {
        $dataValidate = $request->validated();

        DB::beginTransaction();
        try {

            $sucursal->update($dataValidate);

            DB::commit();

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($sucursal, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sucursal  $sucursal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sucursal $sucursal)
    {
        DB::beginTransaction();
        try {
            $sucursal->delete();

            DB::commit();

            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
