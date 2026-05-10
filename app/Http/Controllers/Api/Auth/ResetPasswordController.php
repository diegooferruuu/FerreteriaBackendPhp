<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Traits\ApiResponser;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isNull;

class ResetPasswordController extends Controller
{
    use ApiResponser;

    public function passwordReset(UpdatePasswordRequest $request)
    {
        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }

    // Verify if token is valid
    private function updatePasswordRow($request)
    {
        return DB::table('password_resets')->where([
            'email' => $request->email,
            'token' => $request->token
        ]);
    }

    // Token not found response
    private function tokenNotFoundError()
    {
        $message = 'Su correo electrónico o el token es incorrecto.';
        return $this->ErrorResponse($message, null, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Reset password
    private function resetPassword($request)
    {
        // find email
        $usuario = Usuario::whereEmail($request->email)->first();

        // update password
        $usuario->update([
            'password' => bcrypt($request->password),
            'resent' => 0
        ]);

        // remove verification data from db
        $this->updatePasswordRow($request)->delete();

        // reset password response
        $message = 'La contraseña ha sido actualizada.';
        return $this->SuccessResponse($message, Response::HTTP_CREATED);
    }
}
