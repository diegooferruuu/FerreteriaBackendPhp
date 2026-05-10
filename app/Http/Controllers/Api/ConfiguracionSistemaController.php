<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TipoImpresionResource;
use App\Http\Traits\ApiResponser;
use App\Models\TipoImpresion;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ConfiguracionSistemaController extends Controller
{
    use ApiResponser;
    public function mostrarTipoImpresion()
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return TipoImpresionResource::make(TipoImpresion::first())->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    public function actualizarTipoImpresion()
    {

    }
}
