<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\Codigos\VerificacionNitService;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VerificacionNitController extends Controller
{
    use ApiResponser;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sucursal_id' =>  'required|exists:sucursales,id',
            'cedula_nit'=> 'required', //'required|exists:clientes,cedula_nit'
        ]);
        try {
            $verificacionNitService = new VerificacionNitService();
            $response = $verificacionNitService->handleStore($data);
            $message = "Se verifico correctamente!!";
            return $this->ResponseJson($response, $message);
        } catch (\Throwable $error) {
            $message = 'Verificacion fallida.';
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
