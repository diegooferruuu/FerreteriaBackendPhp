<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTokenDelegadoRequest;
use App\Http\Requests\UpdateTokenDelegadoRequest;
use App\Http\Resources\TokenDelegadoResource;
use App\Http\Traits\ApiResponser;
use App\Models\TokenDelegado;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TokenDelegadoController extends Controller
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
        return TokenDelegadoResource::collection(TokenDelegado::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTokenDelegadoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTokenDelegadoRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidate = $request->validated();

            $data = TokenDelegado::create($dataValidate);

            TokenDelegado::where('estado', 'ACTIVO')->where('id', '!=', $data->id)->update(['estado' => 'INACTIVO']);

            DB::commit();
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }
    public function mostrarTokenActivo()
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return TokenDelegadoResource::make(TokenDelegado::where('estado','ACTIVO')->first())->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TokenDelegado  $tokenDelegado
     * @return \Illuminate\Http\Response
     */
    public function show(TokenDelegado $tokenDelegado)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return TokenDelegadoResource::make($tokenDelegado)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTokenDelegadoRequest  $request
     * @param  \App\Models\TokenDelegado  $tokenDelegado
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTokenDelegadoRequest $request, TokenDelegado $tokenDelegado)
    {
        DB::beginTransaction();
        try {
            $dataValidate = $request->validated();
            $oldStatus = $tokenDelegado->estado;
            $tokenDelegado->update($dataValidate);

            if($oldStatus != $tokenDelegado->estado && $tokenDelegado->estado == 'ACTIVO') {
                TokenDelegado::where('estado', 'ACTIVO')->where('id', '!=', $tokenDelegado->id)->update(['estado' => 'INACTIVO']);
            }

            DB::commit();
            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($tokenDelegado, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TokenDelegado  $tokenDelegado
     * @return \Illuminate\Http\Response
     */
    public function destroy(TokenDelegado $tokenDelegado)
    {
        try {
            $tokenDelegado->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
