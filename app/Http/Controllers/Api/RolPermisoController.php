<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use App\Http\Traits\ApiResponser;
use App\Models\Permiso;
use App\Models\PermisoRol;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RolPermisoController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrador');
        $this->middleware('permission:index-assign')->only('index');
        $this->middleware('permission:store-assign')->only('store');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Rol::all();
        $permisos = Permiso::all();
        $permisoRol = PermisoRol::get();
        $data = array();
        foreach ($permisos as $permiso) {
            foreach ($roles as $rol) {
                $data[$rol->id][$permiso->id] = $permisoRol->where('rol_id', $rol->id)->where('permiso_id', $permiso->id)->count() ? true : false;
            }
        }
        $message = "Registros recuperados correctamente";
        $data = compact('roles', 'permisos', 'data', 'message');
        return response()->json($data);
    }

    public function show(Rol $rol)
    {
        try {
            $message = 'Datos recuperados correctamente.';
            $dataAdicional = $this->SuccessResponse($message);
            return RolResource::make($rol->load('permisos'))->additional($dataAdicional);
        } catch (\Throwable $th) {
            $message = 'Usuario no encontrado!';
            return $this->ErrorResponse($message, $th, Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function assignPermission(Request $request)
    {
        Cache::flush();
        $permiso = Permiso::findorFail($request->permiso_id);
        $estado = $request->input('estado');
        if ($estado) {
            $permiso->roles()->attach($request->rol_id);
            return $this->SuccessResponse('Permiso asignado con éxito.');
        } else {
            $permiso->roles()->detach($request->rol_id);
            return $this->SuccessResponse('Permiso revocado con éxito.');
        }
    }
}
