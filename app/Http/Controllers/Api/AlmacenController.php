<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlmacenRequest;
use App\Http\Requests\UpdateAlmacenRequest;
use App\Http\Resources\AlmacenResource;
use App\Http\Traits\ApiResponser;
use App\Models\Almacen;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AlmacenController extends Controller
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
        $message = "Registros recuperados correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return AlmacenResource::collection(Almacen::filter()->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAlmacenRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlmacenRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidate = $request->validated();

            $data = Almacen::create($dataValidate);

            if( $dataValidate['is_sucursal'] == 1 ) {
                $dataValidate += ['almacen_id' => $data->id_almacen];
                Sucursal::create($dataValidate);
            }

            DB::commit();

            $message = "Se registro correctamente!!";

            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function show(Almacen $almacen)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return AlmacenResource::make($almacen)->additional($dataAdicional);

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAlmacenRequest  $request
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlmacenRequest $request, Almacen $almacen)
    {
        DB::beginTransaction();
        try {
            $dataValidate = $request->validated();
            $almacen->update($dataValidate);
            // $data = Atributo::find($atributo->id);
            if( $dataValidate['is_sucursal'] == 1 ) {

                Sucursal::updateOrCreate(['almacen_id' => $almacen->id_almacen], $dataValidate += ['estado' => 'ACTIVO'] );

            } elseif( $sucursal = Sucursal::where('almacen_id', $almacen->id_almacen)->first() ) {

                $sucursal->update(['estado' => 'INACTIVO']);

            }

            DB::commit();

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($almacen, $message, Response::HTTP_CREATED);

        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Almacen  $almacen
     * @return \Illuminate\Http\Response
     */
    public function destroy(Almacen $almacen)
    {
        DB::beginTransaction();
        try {

            if( Sucursal::where('almacen_id', $almacen->id_almacen)->exists() ) {

                $almacen->sucursal->delete();

            }

            $almacen->delete();

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
