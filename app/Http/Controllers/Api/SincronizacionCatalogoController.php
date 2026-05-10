<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSincronizacionCatalogoRequest;
use App\Http\Requests\UpdateSincronizacionCatalogoRequest;
use App\Http\Resources\CatalogoFacturacionResource;
use App\Http\Resources\SincronizacionCatalogoResource;
use App\Http\Services\Siat\DataSync;
use App\Http\Services\SincronizacionCatalogoService;
use App\Http\Traits\ApiResponser;
use App\Models\CatalogoFacturacion;
use App\Models\Cliente;
use App\Models\PuntoVenta;
use App\Models\SincronizacionCatalogo;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SincronizacionCatalogoController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return SincronizacionCatalogoResource::collection(SincronizacionCatalogo::filter()->paginate($perPage))->additional($dataAdditional);
    }
    public function catalogoFacturacion()
    {
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return CatalogoFacturacionResource::collection(CatalogoFacturacion::orderby('id')->get())->additional($dataAdditional);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSincronizacionCatalogoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSincronizacionCatalogoRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();
            //existe el catalogo null o true
            $dataValoresCatalogoExiste = ValorCatalogo::where('sincronizacion_catalogo_id',6)->where('codigo_clasificador',1)->exists();
//            dd(!$dataValoresCatalogoExiste);
            if( $request->has('catalogo_facturacion_id') ) {
                SincronizacionCatalogoService::sync($dataValidated);
            } else {
                SincronizacionCatalogoService::syncAll($dataValidated);
            }

            //consultamos el catalogo NIT
            $dataValoresCatalogo = ValorCatalogo::where('sincronizacion_catalogo_id',6)->where('codigo_clasificador',5)->first();

            //si falso
            if (!$dataValoresCatalogoExiste)
            {
//                Cliente::create([
//                    'razon_social' => 'SIN NOMBRE',
//                    'cedula_nit' => '0',
//                    'complemento' => '',
//                    'telefono' => '',
//                    'email' => '',
//                    'direccion' => '',
//                    'departamento_id' => 1,
//                    'tipo_documento_id' => $dataValoresCatalogo->id
//                ]);
                Cliente::create([
                    'razon_social' => '',
                    'cedula_nit' => '99001',
                    'complemento' => '',
                    'telefono' => '',
                    'email' => '',
                    'direccion' => '',
                    'departamento_id' => 1,
                    'tipo_documento_id' => $dataValoresCatalogo->id
                ]);
                Cliente::create([
                    'razon_social' => 'Control Tributario',
                    'cedula_nit' => '99002',
                    'complemento' => '',
                    'telefono' => '',
                    'email' => '',
                    'direccion' => '',
                    'departamento_id' => 1,
                    'tipo_documento_id' => $dataValoresCatalogo->id
                ]);
                Cliente::create([
                    'razon_social' => 'VENTAS MENORES DEL DÍA',
                    'cedula_nit' => '99003',
                    'complemento' => '',
                    'telefono' => '',
                    'email' => '',
                    'direccion' => '',
                    'departamento_id' => 1,
                    'tipo_documento_id' => $dataValoresCatalogo->id
                ]);
            }


            DB::commit();
            return $this->SuccessResponse('Sincronizacion realizada correctamente!', Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido!";
            if($error->getMessage())
            {
                $message = $error->getMessage();
            }
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SincronizacionCatalogo  $sincronizacionCatalogo
     * @return \Illuminate\Http\Response
     */
    public function show(SincronizacionCatalogo $sincronizacionCatalogo)
    {
        try {
            $dataAdicional = $this->SuccessResponse('Registro recuperado correctamente!');
            return SincronizacionCatalogoResource::make($sincronizacionCatalogo)->additional($dataAdicional);
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Obteción de datos fallida.', $error, Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSincronizacionCatalogoRequest  $request
     * @param  \App\Models\SincronizacionCatalogo  $sincronizacionCatalogo
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSincronizacionCatalogoRequest $request, SincronizacionCatalogo $sincronizacionCatalogo)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SincronizacionCatalogo  $sincronizacionCatalogo
     * @return \Illuminate\Http\Response
     */
    public function destroy(SincronizacionCatalogo $sincronizacionCatalogo)
    {
        //
    }

    public function syncAll() {
        return SincronizacionCatalogoService::syncAll();
    }

    public function syncDateTime(StoreSincronizacionCatalogoRequest $request) {
        try {
            //pos, /id
            $dataValidated = $request->validated();

            if($dataValidated['syncable_type'] === 'sucursal')
            {
                $casaMatriz = Sucursal::with('cuis')->where('codigo_siat', '0')->firstOrFail();
                if( !$casaMatriz->cuis ) {
                    throw new \Exception("Establece un CUIS para la sucursal(casa matriz)");

                }
                $params = ['cuis' => $casaMatriz->cuis->valor, 'branch_code' => 0, 'pos_code' => 0];

            }

            if($dataValidated['syncable_type'] === 'pos')
            {
                $casaMatriz = Sucursal::with('cuis')->where('codigo_siat', '0')->firstOrFail();
                $dataPos = PuntoVenta::with('cuis')->where('id',$dataValidated['syncable_id'])->first();

                if( !$casaMatriz->cuis ) {
                    throw new \Exception("Establece un CUIS para la sucursal(casa matriz)");

                }
                if( !$dataPos->cuis ) {
                    throw new \Exception("Establece un CUIS para la Punto venta");

                }
                $params = ['cuis' => $dataPos->cuis->valor, 'branch_code' => 0, 'pos_code' => $dataPos->codigo_siat];

            }
            $serviceDataSync = new DataSync();
            $response = $serviceDataSync->syncDateTime($params);
            if($response->RespuestaFechaHora->transaccion)
            {
                $fecha = $response->RespuestaFechaHora->fechaHora;
                $fechaFormat = Carbon::parse($fecha)->format('Y-m-d H:i:s');
                exec("echo 'P4ssw0rd' | sudo -S date --set '{$fechaFormat}'");
            }
            return $response;
        } catch (\Throwable $error) {
//            throw $error;
            return $error->getMessage();
        }
    }
}
