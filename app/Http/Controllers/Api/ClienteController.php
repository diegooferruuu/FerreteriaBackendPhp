<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClienteResource;
use App\Http\Services\Codigos\VerificacionNitService;
use App\Http\Traits\ApiResponser;
use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClienteController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware(['permission:vista-contactos','permission:listar-cliente'])->only('index');
        $this->middleware(['permission:vista-contactos','permission:crear-cliente'])->only('store','buscarClienteVenta');
        $this->middleware(['permission:vista-contactos','permission:editar-cliente'])->only('update','show');
        $this->middleware(['permission:vista-contactos','permission:eliminar-cliente'])->only('destroy');
//        $this->middleware(['permission:vista-contactos','permission:ver-producto'])->only('show');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->query('perPage',10);
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);

        return ClienteResource::collection(Cliente::filter()->paginate($perPage))->additional($dataAdditional);

    }
    public function buscarClienteVenta(Request $request)
    {
        $cedula_nit = $request->query('cedula_nit',null);
        $search = $request->query('search',null);
        try {
            $message = "Registros recuperado correctamente!!";
            $dataAdditional = $this->SuccessResponse($message);
            if (is_null($search))
            {
                $clienteBuscado = Cliente::where('cedula_nit',$cedula_nit)->first();
                if(!is_null($clienteBuscado))
                {
                    return ClienteResource::make($clienteBuscado)->additional($dataAdditional);
                }else{
                    return response()->json([
                        'message' => 'Sin registros',
                        'data' => 0,
                        'success' => true
                    ]);
                }
            }else{
                return ClienteResource::collection(Cliente::whereLike(['razon_social'],$search)->paginate(5))->additional($dataAdditional);
            }

        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreClienteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClienteRequest $request)
    {
        try {
            $dataValidated = $request->validated();

            $codigoDocumentoId = ValorCatalogo::find($dataValidated['tipo_documento_id'])['codigo_clasificador'];
            $idSucursal = Sucursal::where('codigo_siat',0)->first()['id'];

            if($codigoDocumentoId == '5')
            {
                $dataValidated['sucursal_id'] = $idSucursal;
                $verificacionNitService = new VerificacionNitService();
                $verificacionNitService->handleStore($dataValidated);
                $dataValidated['verificacion']=1;
            }
            $data = Cliente::create($dataValidated);
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Registro fallido";
            $statusCode = Response::HTTP_PRECONDITION_FAILED;

            if ($error->getMessage())
            {
                $message = $error->getMessage();
            }
            if ($error->getCode() != 0)
            {
                $statusCode = $error->getCode();
            }
            return $this->ErrorResponse($message, $error, $statusCode);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function show(Cliente $cliente)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return ClienteResource::make( $cliente->load('tipoDocumento') )->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateClienteRequest  $request
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        try {
            $dataValidated = $request->validated();
            $cliente->update($dataValidated);
            // $data = Atributo::find($atributo->id);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($cliente, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cliente  $cliente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();
            $message = "Se Elimino correctamente!!";
            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_NO_CONTENT);
        }
    }
}
