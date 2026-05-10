<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCufdRequest;
use App\Http\Requests\UpdateCufdRequest;
use App\Http\Resources\CufdResource;
use App\Http\Services\CufdService;
use App\Http\Traits\ApiResponser;
use App\Models\Cufd;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CufdController extends Controller
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
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctemente!');
        return CufdResource::collection(Cufd::filter()->paginate($perPage))->additional($dataAdditional);
    }
    public function cufdStatus()
    {
        $status = request('status');

        $dataAdditional = $this->SuccessResponse('Registros recuperados correctemente!');
        return CufdResource::collection(Cufd::where('estado',$status)->get())->additional($dataAdditional);
    }
    public function cufdFecha()
    {
        $idCuis = request('cuis_id');
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctemente!');
//        dd(Carbon::now()->subHour(48)->format('y-m-d H:m:s.u'));
        return CufdResource::collection(Cufd::where('created_at','>',Carbon::now()->subHour(48))->where('cuis_id',$idCuis)->OrderBy('id')->get())->additional($dataAdditional);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCufdRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCufdRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();
            $cufdService = new CufdService();
            $data = $cufdService->handleStore($dataValidated);

            DB::commit();
            return $this->CreatedResponse(new CufdResource($data), 'Se registro correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            return $error->getMessage();
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cufd  $cufd
     * @return \Illuminate\Http\Response
     */
    public function show(Cufd $cufd)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCufdRequest  $request
     * @param  \App\Models\Cufd  $cufd
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCufdRequest $request, Cufd $cufd)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cufd  $cufd
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cufd $cufd)
    {
        //
    }
}
