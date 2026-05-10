<?php

namespace App\Http\Controllers\Api;

use App\Exports\TemplateImportPricesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCargaPrecioRequest;
use App\Http\Requests\StorePrecioMasivoRequest;
use App\Http\Requests\UpdateCargaPrecioRequest;
use App\Http\Resources\CargaPrecioResource;
use App\Http\Services\CargaPrecioService;
use App\Http\Traits\ApiResponser;
use App\Imports\PrecioGeneralImport;
use App\Imports\PrecioParticularImport;
use App\Models\CargaPrecio;
use App\Models\Inventario;
use App\Models\PrecioGeneral;
use App\Models\PrecioParticular;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CargaPrecioController extends Controller
{
    use ApiResponser;

    protected $cargaPrecioService;

    public function __construct(CargaPrecioService $cargaPrecioService)
    {
        $this->cargaPrecioService = $cargaPrecioService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $dataAdditional = $this->SuccessResponse('Registros recuperados correctamente!');
        return CargaPrecioResource::collection(
            CargaPrecio::filter()->paginate($perPage)
        )->additional($dataAdditional);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CargaPrecio  $cargaPrecio
     * @return \Illuminate\Http\Response
     */
    public function show(CargaPrecio $cargaPrecio)
    {
        try {
            $dataAdicional = $this->SuccessResponse('Registro recuperado correctamente!');
            return CargaPrecioResource::make($cargaPrecio)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CargaPrecio  $cargaPrecio
     * @return \Illuminate\Http\Response
     */
    public function destroy(CargaPrecio $cargaPrecio)
    {
        //
    }

    /**
     * Import prices from excel.
     *
     * @param  \App\Http\Requests\StorePrecioMasivoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function import(StorePrecioMasivoRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->safe()->except(['archivo']);

            $cargaPrecio = CargaPrecio::create($validated);
            $validated['carga_precio_id'] = $cargaPrecio->id;

//            Excel::import(new PrecioGeneralImport($validated,$validated['descripcion']), $request->file('archivo'));

            Excel::import(new PrecioGeneralImport($validated,$validated['descripcion']), $request->file('archivo'));

            DB::commit();
            return $this->SuccessResponse('Se importo correctamente!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $error) {
            DB::rollBack();
            if($error->failures()) {
                $errors = array_map(function($fail) {
                    return [
                        'productos.' . $fail->row() . '.' . $fail->attribute() => $fail->errors()
                    ];
                }, $error->failures());

                return response()->json([
                    'message' => 'Error en validación!',
                    'errors' => $errors
                ], Response::HTTP_PRECONDITION_FAILED);
            }
            return $this->ErrorResponse('Importacion fallida!', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Store many prices.
     *
     * @param \App\Http\Requests\StorePrecioMasivoRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeMany(StorePrecioMasivoRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $cargaPrecio = CargaPrecio::create($validated);

            foreach ($validated['precios'] as $precio)
            {
//                dd($precio,$validated['descripcion'],$precio['producto_id']);
                $data = [
                    'producto_id' => $precio['producto_id'],
                    'carga_precio_id' => $cargaPrecio->id,
                ];
                if($validated['descripcion'] == 'mayor')
                {
                    $data['precio_mayor'] = $precio['precio_mayor'];
                }else if($validated['descripcion']== 'menor'){
                    $data['precio_menor'] = $precio['precio_menor'];
                }
                else {
                    $data['precio_menor'] = $precio['precio_menor'];
                    $data['precio_mayor'] = $precio['precio_mayor'];
                }
//                dd($data);
                PrecioGeneral::updateOrCreate(['producto_id' => $precio['producto_id']], $data);

            }
            DB::commit();
            return $this->SuccessResponse('Precios registrados correctamente!');
        } catch (\Throwable $error) {
            DB::rollBack();
            return $this->ErrorResponse('Registro de precios fallido!', $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Download excel template to import prices
     */
    public function exportTemplate() {
        return Excel::download(new TemplateImportPricesExport, 'plantilla_importacion_precios.xlsx');
    }
}
