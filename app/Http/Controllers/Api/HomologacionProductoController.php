<?php

namespace App\Http\Controllers\Api;

use App\Exports\TemplateImportHomologationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHomologacionProductoRequest;
use App\Http\Requests\UpdateHomologacionProductoRequest;
use App\Http\Resources\HomologacionProductoResource;
use App\Http\Traits\ApiResponser;
use App\Imports\HomologacionProductoImport;
use App\Models\HomologacionProducto;
use App\Models\Producto;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class HomologacionProductoController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
	 $perPage = request('perPage') && is_numeric( request('perPage') ) ? request('perPage') : 10;
        $search = $request->query('search',null);
        $message = "Registros recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return HomologacionProductoResource::collection(Producto::whereLike(['descripcion'],$search)->paginate($perPage))->additional($dataAdditional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreHomologacionProductoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHomologacionProductoRequest $request)
    {
        try {
            $dataValidatedd = $request->validated();
            $data = HomologacionProducto::create($dataValidatedd);
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HomologacionProducto  $homologacionProducto
     * @return \Illuminate\Http\Response
     */
    public function show(HomologacionProducto $homologacionProducto)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return HomologacionProductoResource::make($homologacionProducto)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateHomologacionProductoRequest  $request
     * @param  \App\Models\HomologacionProducto  $homologacionProducto
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHomologacionProductoRequest $request, HomologacionProducto $homologacionProducto)
    {
        try {
            $dataValidated = $request->validated();
            $homologacionProducto->update($dataValidated);
            // $data = Atributo::find($atributo->id);

            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($homologacionProducto, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HomologacionProducto  $homologacionProducto
     * @return \Illuminate\Http\Response
     */
    public function destroy(HomologacionProducto $homologacionProducto)
    {
        //
    }

    /**
     * Import prices from excel.
     *
     * @param  \App\Http\Requests\StorePrecioMasivoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function import(StoreHomologacionProductoRequest $request)
    {
        DB::beginTransaction();
        try {
            Excel::import(new HomologacionProductoImport, $request->file('archivo'));
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
     * Download excel template to import prices
     */
    public function exportTemplate() {
        try {
            return Excel::download(new TemplateImportHomologationExport, 'plantilla_homologacion_productos.xlsx');
        } catch (\Throwable $error) {
            return $this->ErrorResponse('Exportacion fallida!', $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
