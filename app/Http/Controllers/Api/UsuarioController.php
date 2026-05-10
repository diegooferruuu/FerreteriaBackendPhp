<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\TipoImpresion;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Perfil;
use App\Http\Traits\ApiResponser;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    use ApiResponser;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
//        $this->middleware('role:administrador', ['except' => ['passwordChange']]);
        $this->middleware('permission:index-user')->only('index');
        $this->middleware('permission:store-user')->only('store');
        $this->middleware('permission:show-user')->only('show');
        $this->middleware('permission:update-user')->only('update','passwordChange');
        $this->middleware('permission:destroy-user')->only('destroy');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $perPage = request('perPage') && is_numeric(request('perPage')) ? request('perPage') : 10;
            $message = "Registros recuperado con éxito.";
            $dataAdditional = $this->SuccessResponse($message);
            return UsuarioResource::collection(Usuario::filter()->paginate($perPage))->additional($dataAdditional);
        } catch (\Throwable $th) {
            $message = 'Obtención de registros fallida!';
            return $this->ErrorResponse($message, $th, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUsuarioRequest $request)
    {
        try {
            DB::beginTransaction();
            // $username = $request->username;
            $email = $request->email;
            $username = $request->username;
            $password = $request->password;
            $nombres = $request->nombre;
            $apellidos = $request->apellidos;
            $telefono = $request->telefono;
            $celular = $request->celular;
            $rol  = $request->rol;

            if ($request->file('foto')) {
                $foto = Perfil::setImagen($request->file('foto'));
            } else {
                $foto = "Sin foto";
            }

            $usuario = Usuario::create([
                'username' => $username,
                'password' => Hash::make($password),
                'email' => $email,
            ]);

            Perfil::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'celular' => $celular,
                'foto' => $foto,
                'usuario_id' => $usuario->id,
            ]);

            if ($rol) {
                $usuario->roles()->sync($rol);
//                $role = Rol::where('rol', $rol)->first();
            } else {
                $usuario->roles()->sync(1);
//                $role = Rol::where('slug', 'invitado')->first();
            }
            //asignacion impresion
            $impresionPagina = TipoImpresion::where('tipo','pagina')->first();

            $usuario->tipoImpresion()->attach($impresionPagina->id);

            DB::commit();

            $message = "Usuario creado con éxito.";
            return $this->CreatedResponse($usuario, $message, Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = "Creación de usuario fallida!";
            return $this->ErrorResponse($message, $th, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($idUsuario)
    {
        try {
            $dataUsuario = Usuario::with('perfil','roles')->where('id',$idUsuario)->first();

//            dd(json_decode($dataUsuario));
//            $usuario = Usuario::join('perfiles', 'perfiles.usuario_id', '=', 'usuarios.id')
//                ->join('usuario_rol', 'usuarios.id', '=', 'usuario_rol.usuario_id')
//                ->join('roles', 'usuario_rol.rol_id', '=', 'roles.id')
//                ->select('usuarios.id', 'nombres', 'apellidos', 'username', 'email', 'foto', 'telefono', 'celular', 'rol', 'estado')
//                ->where('usuarios.id', $idUsuario)
//                ->first();
            $message = 'Datos recuperados correctamente.';
            return $this->ResponseJson($dataUsuario, $message);
        } catch (\Throwable $th) {
            $message = 'Usuario no encontrado!';
            return $this->ErrorResponse($message, $th, Response::HTTP_BAD_REQUEST);
        }
    }
    public function updateTipoImpresion(Request $request)
    {

        try {
            $dataValidate = $request->validate([
                'id' => 'required|integer|exists:usuarios,id',
                'tipo' => 'required|string|in:rollo,pagina',
            ]);
            //tipo a reasignar
            $tipo = $dataValidate['tipo'] == "pagina" ? "rollo" : "pagina";

            $usuario = Usuario::find($dataValidate['id']);
            $dataTipoImpresion = TipoImpresion::where('tipo',$tipo)->first();
            //reasignacion y asignacion
            $usuario->tipoImpresion()->detach();
            $usuario->tipoImpresion()->attach($dataTipoImpresion->id);
            DB::commit();
            $message = "Se reasigno a ". strtoupper($tipo) ." correctamente !!";
            return $this->CreatedResponse($tipo, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
            }


    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        try {
            DB::beginTransaction();
            $email = $request->email;
            $username = $request->username;
            $estado = $request->estado;
            $nombres = $request->nombre;
            $apellidos = $request->apellidos;
            $telefono = $request->telefono;
            $celular = $request->celular;

            $usuario->update([
                'username' => $username,
                'email' => $email,
                'estado' => $estado,
            ]);
            $rol  = $request->rol;
            if ($rol) {
                $usuario->roles()->sync($rol);
            } else {
                $usuario->roles()->sync(1);
            }

            $idPerfil = $usuario->perfil;

            $perfil = Perfil::findOrFail($idPerfil->id);
            $foto = null;
            if ($request->file('foto')) {
                $foto = Perfil::setImagen($request->file('foto'), $perfil->foto);
            }
            $perfil->update([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'celular' => $celular,
                'foto' => $foto,
                'usuario_id' => $usuario->id,
            ]);

            DB::commit();
            $message = "Usuario actualizado con éxito.";
            return $this->CreatedResponse($usuario, $message);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Actualización de usuario fallida!";
//            return $this->ErrorResponse($message, $th, Response::HTTP_NOT_MODIFIED);
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordChange(PasswordRequest $request)
    {
        try {
            $oldPassword = $request->oldpassword;
            $newpassword = $request->password;
            $idUsuario = auth()->user()->id;

            /* Verificamos que el usuario tenga cuenta activa */
            $usuario = Usuario::where('estado', 'ACTIVO')->where('id', $idUsuario)->first();

            if ($usuario) {
                /* Verificamos si el password anterior es el mismo */
                if (!Hash::check($oldPassword, $usuario->password)) {
                    $message = 'Contraseña anterior no es la correcta!';
                    return $this->ErrorResponse($message, null, Response::HTTP_PRECONDITION_FAILED);
                }

                $usuario->password = Hash::make($newpassword);
                $usuario->save();

                $message = 'Contraseña actualizada con éxito.';
                return $this->SuccessResponse($message);
            }
        } catch (\Throwable $th) {
            $message = 'No se pudo actualizar la contraseña!';
            return $this->ErrorResponse($message, null, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $idUsuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Usuario $usuario)
    {
        try {
            $usuario->delete();

            $idPerfil = $usuario->perfil;
            $perfil = Perfil::findOrFail($idPerfil->id);
            $perfil->delete();

            $message = 'Usuario eleminado con éxito.';
            return $this->SuccessResponse($message);
        } catch (\Throwable $th) {
            $message = 'Eliminación de usario fallida!';
            return $this->ErrorResponse($message, null, Response::HTTP_NO_CONTENT);
        }
    }
}
