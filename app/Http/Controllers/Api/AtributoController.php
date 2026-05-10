<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAtributoRequest;
use App\Http\Requests\UpdateArrayProductoRequest;
use App\Http\Requests\UpdateAtributoRequest;
use App\Http\Resources\AtributoResource;
use App\Http\Traits\ApiResponser;
use App\Models\Atributo;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class AtributoController extends Controller
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
        return AtributoResource::collection(Atributo::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAtributoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAtributoRequest $request)
    {
        try {
            $dataValidate = $request->validated();
            $data = Atributo::create($dataValidate);
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
     * @param  \App\Models\Atributo  $atributo
     * @return \Illuminate\Http\Response
     */
    public function show(Atributo $atributo)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return AtributoResource::make($atributo)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAtributoRequest  $request
     * @param  \App\Models\Atributo  $atributo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAtributoRequest $atributoRequest, UpdateArrayProductoRequest $productosRequest, Atributo $atributo)
    {
        try {
            $atributoValidated = $atributoRequest->validated();

            $productosValidated = Arr::get( $productosRequest->validated(), 'productos', []);

            $atributo->update($atributoValidated);
            
            // adicionando o eliminando relaciones con productos
            if( count( $productosValidated ) > 0 ) {
                foreach ($productosValidated as $producto) {
                    if( $producto['action'] == 'sync' ) {
                        $atributo->productos()->syncWithoutDetaching([ $producto['id'] => [ 'valor' => $producto['valor'] ] ]);
                        continue;
                    }
                    if( $producto['action'] == 'detach' ) {
                        $atributo->productos()->updateExistingPivot($producto['id'], [
                            'deleted_at' => Carbon::now(),
                        ]);
                        continue;
                    }
                }
            }

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($atributo, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Atributo  $atributo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Atributo $atributo)
    {
        try {
            $atributo->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
