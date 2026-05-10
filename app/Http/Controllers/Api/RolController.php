<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolRequest;
use App\Http\Resources\RolResource;
use App\Http\Traits\ApiResponser;
use App\Models\Permiso;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    use ApiResponser;
    /**
     * Instantiate a new UserController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrador');
        $this->middleware('permission:index-role')->only('index');
        $this->middleware('permission:store-role')->only('store');
        $this->middleware('permission:show-role')->only('show');
        $this->middleware('permission:update-role')->only('update');
        $this->middleware('permission:destroy-role')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $message = "Registros recuperados con éxito";
            $dataAdditional = $this->SuccessResponse($message);
            return RolResource::collection(Rol::all())->additional($dataAdditional);
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
    public function store(RolRequest $request)
    {

        try {
            DB::beginTransaction();
            $rol = Rol::create([
                'rol' => $request->rol,
                'slug' => Str::slug($request->rol, '-'),
                'descripcion' => $request->descripcion,
            ]);
            DB::commit();
            $message = 'Rol creado con éxito';
            return $this->ResponseJson($rol, $message, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = "Creación de rol fallida!";
            return $this->ErrorResponse($message, $th, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RolRequest $request, Rol $rol)
    {
        try {
            DB::beginTransaction();
            $rol->update([
                'rol' => $request->rol,
                'slug' => Str::slug($request->rol, '-'),
                'descripcion' => $request->descripcion,
            ]);

            $permisosId = $request->get('permisos', []);
            dd($permisosId);
            $permisos = Permiso::allowed()->whereIn('id', $permisosId)->get();
            DB::commit();
            $message = 'Rol actualizado con éxito.';
            return $this->CreatedResponse($rol, $message);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = 'Actualización de rol fallida.';
            return $this->ErrorResponse($message, $th, Response::HTTP_NOT_MODIFIED);
        }
    }

    /**
     * Cambia de rol de un usuario en especificado.
     *
     * @param  int  $idUsuario
     * @return \Illuminate\Http\Response
     */
    public function cambiarRol(Request $request, $idUsuario)
    {
        $rol = $request->rol;
        if ($rol == 'administrador' || $rol == 'Administrador' || $rol == 'admin' || $rol == 'Admin') {
            $message = 'No tienes autorización para asignar este rol';
            return $this->ResponseJson($message, Response::HTTP_FORBIDDEN);
        } else {

            $usuario = Usuario::findorFail($idUsuario);
            $rol = Rol::where('slug', $rol)->first();

            $usuario->roles()->sync($rol->id);

            $message = 'Cambio de rol con éxito.';
            return $this->SuccessResponse($message);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Rol  $rol
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rol $rol)
    {
        try {
            $rol->delete();
            $message = 'Rol eliminado con éxito';
            return $this->SuccessResponse($message);
        } catch (\Throwable $th) {
            $message = 'Eliminación de rol fallida!';
            return $this->ErrorResponse($message, $th, Response::HTTP_NO_CONTENT);
        }
    }
}
