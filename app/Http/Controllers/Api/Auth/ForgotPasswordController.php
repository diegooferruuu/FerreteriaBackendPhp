<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Traits\ApiResponser;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResetEmail(ForgotPasswordRequest $request)
    {
        try {
            $usuario = Usuario::where('estado', 'ACTIVO')
                ->where('email', $request->email)
                ->first();

            if (!$usuario) {
                $message = "El correo electrónico no existe!";
                return $this->SuccessResponse($message, Response::HTTP_NOT_FOUND);
            } else {
                // En caso afirmativo, mostramos un mensaje Llegamos al límite y evitamos enviar más correos electrónicos
                if ($usuario->resent >= 3) {
                    $message = 'Límite de solicitudes de (' . $usuario->resent . ') excedido.';
                    return $this->SuccessResponse($message, Response::HTTP_TOO_MANY_REQUESTS);
                }

                $perfil = Perfil::where('usuario_id', $usuario->id)->first();
                $this->sendMail($request->email, $perfil);

                $usuario->resent++;
                $usuario->save();

                $message = 'Enlace de restablecimiento de contraseña enviado.';
                return $this->SuccessResponse($message);
            }
        } catch (\Throwable $th) {
            $message = 'No se pudo enviar el enlace de restablecimiento de contraseña!';
            return $this->ErrorResponse($message, null, Response::HTTP_REQUEST_TIMEOUT);
        }
    }

    //send mail
    public function sendMail($email, $perfil)
    {
        $token = $this->generateToken($email);
        $data = [
            'perfil' => $perfil->nombres .  ' ' . $perfil->apellidos,
            'url' => config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . $email
        ];
        Mail::to($email)->send(new SendMail($data));
    }

    //generate token
    public function generateToken($email)
    {
        $isOtherToken = DB::table('password_resets')->where('email', $email)->first();
        if ($isOtherToken) {
            return $isOtherToken->token;
        }
        $token = Str::random(80);;
        $this->storeToken($token, $email);
        return $token;
    }

    //store password_resets data
    public function storeToken($token, $email)
    {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }
}
