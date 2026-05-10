<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFirmaRequest;
use App\Http\Resources\FirmaResource;
use App\Http\Traits\ApiResponser;
use App\Models\Firma;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FirmaController extends Controller
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
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctamente!!');
        return FirmaResource::collection(Firma::paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFirmaRequest $request)
    {
        DB::beginTransaction();
        try {

            $dataValidated = $request->validated();

            $dataValidated['certificado'] = $request->file('certificado')->storeAs('certificado', uniqid().'.crt.pem', 'private');
            $dataValidated['llave_privada'] = $request->file('llave_privada')->storeAs('certificado', uniqid().'.key.pem', 'private');

            Firma::where('estado', 'ACTIVO')->update(['estado' => 'INACTIVO']);

            $data = Firma::create($dataValidated);

            DB::commit();
            return $this->CreatedResponse($data, 'Se registro  correctamente!!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            return $this->ErrorResponse('Registro fallido', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function show(Firma $firma)
    {
        try {
            $dataAdditional = $this->SuccessResponse('Registro recuperado correctamente!!');
            return FirmaResource::make($firma)->additional($dataAdditional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obtencion de datos fallida.', $error, Response::HTTP_BAD_REQUEST);
        }
    }
    public function mostrarFirmaActivo()
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return FirmaResource::make(Firma::where('estado','ACTIVO')->first())->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Firma $firma)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function destroy(Firma $firma)
    {
        try {
            $firma->delete();
            return $this->SuccessResponse('Se elimino correctamente!!');
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Eliminacion fallida', $error, Response::HTTP_NO_CONTENT);
        }
    }
}
