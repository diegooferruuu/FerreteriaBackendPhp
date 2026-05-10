<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use Illuminate\Http\Response;
use App\Http\Requests\PermisoRequest;
use App\Http\Resources\PermisoResource;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermisoController extends Controller
{
    use ApiResponser;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:administrador');
        $this->middleware('permission:index-permission')->only('index');
        $this->middleware('permission:store-permission')->only('store');
        $this->middleware('permission:update-permission')->only('update');
        $this->middleware('permission:destroy-permission')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $perPage = request('perPage') && is_numeric(request('perPage')) ? request('perPage') : 10;
            $message = "Registros recuperados correctamente.";
            $dataAdditional = $this->SuccessResponse($message);
            return PermisoResource::collection(Permiso::filter()->paginate($perPage))->additional($dataAdditional);
        } catch (\Throwable $th) {
            $message = 'Obtención de registros fallida!';
            return $this->ErrorResponse($message, $th, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermisoRequest $request)
    {
//        dd($request);
        try {
            DB::beginTransaction();
            $permiso = Permiso::create([
                'permiso' => $request->permiso,
            ]);
            DB::commit();
            $message = "Permiso creado con éxito.";
            return $this->CreatedResponse($permiso, $message, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = "Creación de permiso fallida!";
            return $this->ErrorResponse($message, $th, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permiso  $permiso
     * @return \Illuminate\Http\Response
     */
    public function show(Permiso $permiso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permiso  $permiso
     * @return \Illuminate\Http\Response
     */
    public function update(PermisoRequest $request, Permiso $permiso)
    {
        try {
            DB::beginTransaction();
            $slug = Str::slug($request->permiso, '-');
            $permiso->update([
                'permiso' => $request->permiso,
                'slug' => $slug,
            ]);
            DB::commit();
            $message = 'Permiso actualizado con éxito.';
            return $this->CreatedResponse($permiso, $message);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = "Actualización de permiso fallida!";
            return $this->ErrorResponse($message, $th, Response::HTTP_NOT_MODIFIED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permiso  $permiso
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permiso $permiso)
    {
        try {
            $permiso->delete();
            $message = 'Permiso eliminado con éxito.';
            return $this->SuccessResponse($message);
        } catch (\Throwable $th) {
            $message = 'Eliminación de permiso fallida!';
            return $this->ErrorResponse($message, $th, Response::HTTP_NO_CONTENT);
        }
    }
}
