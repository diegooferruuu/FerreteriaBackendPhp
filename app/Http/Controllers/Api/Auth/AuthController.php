<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use App\Http\Traits\ApiResponser;
use App\Models\Rol;
use App\Models\Usuario;

class AuthController extends Controller
{
    use ApiResponser;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(AuthRequest $request)
    {
        try {
            $email = $request->email;
            $username = $request->username;

            /* verifico si el usuario exite y tenga un cuenta activa */
//            $usuarioData = Usuario::where('email', $email)->where('estado', 'ACTIVO')->first();
            $usuarioData = Usuario::where('username', $username)->where('estado', 'ACTIVO')->first();

            if (!$usuarioData) {
                $message = 'Cuenta inactiva contactece con un administrador.';
                return $this->ErrorResponse($message, null, Response::HTTP_BAD_REQUEST);
            } else {
                $credentials = $request->only('username', 'password');

                $token = Auth::attempt($credentials);

                if (!$token) {
                    $message = 'El usuario y la contraseña no coinciden con nuestro registro.';
                    return $this->ErrorResponse($message, null, Response::HTTP_BAD_REQUEST);
                }

                return $this->createNewToken($token);
            }
        } catch (\Throwable $th) {
            $message = 'No se puede iniciar la sesión.';
            return $this->ErrorResponse($message, null, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            Auth::logout();
            $message = 'Sesión cerrada con éxito.';
            return $this->SuccessResponse($message);
        } catch (\Throwable $th) {
            $message = 'Lo siento, no se puede cerrar la sesión.';
            return $this->ErrorResponse($message, $th, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Refresh a token.
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = Auth::refresh();
        return $this->createNewToken($token);
    }

    /**
     * Get the token array structure.
     * @param  string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $idUsuario = auth()->user()->id;

        $dataUsuario = Usuario::with('perfil','tipoImpresion')->where('id',$idUsuario)->first();
        $usuario = [
            'id' => $dataUsuario->id,
            'nombres' => $dataUsuario->perfil->nombres,
            'apellidos' => $dataUsuario->perfil->apellidos,
            'username' => $dataUsuario->username,
            'email' => $dataUsuario->email,
            'foto'=> $dataUsuario->perfil->foto,
            'telefono'=> $dataUsuario->perfil->telefono,
            'celular'=> $dataUsuario->perfil->celular,
            'rol' => $dataUsuario->roles[0]->rol,
            'estado'=> $dataUsuario->estado,
            'tipo_impresion' => $dataUsuario->tipoImpresion[0]->tipo
        ];

        $permisos =  collect([]);
        foreach ($dataUsuario->roles as $rol)
        {
            $permisos = $permisos->merge($rol->permisos->pluck('slug'));
        }

        $minutes = auth()->factory()->getTTL();
        $expires_at = Carbon::now()->addMinutes($minutes-1)->format('Y-m-d H:i:s');

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión con éxito',
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expires_at,
            'user' => $usuario,
            'permissions' => $permisos->unique()->values()->all()
        ], Response::HTTP_OK);
    }
}
