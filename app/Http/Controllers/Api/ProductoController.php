<?php

namespace App\Http\Controllers\Api;

use App\Exports\TemplateImportProductExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrecioMasivoRequest;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Http\Resources\ProductoResource;
use App\Imports\PrecioGeneralImport;
use App\Imports\ProductosImport;
use App\Models\Atributo;
use App\Models\CargaPrecio;
use App\Models\PrecioGeneral;
use App\Models\Procedencia;
use App\Models\Producto;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\FileUpload;
use App\Imports\ProductoImport;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    use ApiResponser, FileUpload;

    public function __construct()
    {
        $this->middleware(['permission:vista-gestion-productos','permission:listar-producto'])->only('index');
        $this->middleware(['permission:vista-gestion-productos','permission:crear-producto'])->only('store');
        $this->middleware(['permission:vista-gestion-productos','permission:editar-producto'])->only('update');
        $this->middleware(['permission:vista-gestion-productos','permission:anular-producto'])->only('destroy');
        $this->middleware(['permission:vista-gestion-productos','permission:ver-producto'])->only('show');
        $this->middleware(['permission:vista-gestion-productos','permission:importar-producto'])->only('exportTemplate');
        $this->middleware(['permission:vista-gestion-productos','permission:exportar-producto'])->only('import');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
	$perPage = request('perPage') && is_numeric(request('perPage')) ? request('perPage') : 10;
        $search = $request->query('like',null);
        $message = "Registro recuperado correctamente!!";
        $dataAdditional = $this->SuccessResponse($message);
        return ProductoResource::collection(
            Producto::with('precioGeneral', 'clasificacionProducto', 'atributos','procedencia')
                ->whereLike(['descripcion'],$search)
                ->orWhereHas('procedencia', function ($query) use ($search) {
                    $query->whereLike(['procedencia'],$search);
                })
                ->orderBy('id')->paginate($perPage))->additional($dataAdditional);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreProductoRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductoRequest $request)
    {
        DB::beginTransaction();
        try {

            $dataValidated = $request->validated();

            $unidadMedidaBuscado = UnidadMedida::where('unidad_medida', $dataValidated['unidad_medida'])->first();

            if (!is_null($unidadMedidaBuscado)) {
                $dataValidated['unidad_medida_id'] = $unidadMedidaBuscado->id;
            } else {
                $unidadMedida = UnidadMedida::create([
                    'unidad_medida' => $dataValidated['unidad_medida'],
                    'valor_catalogo_id' => $dataValidated['codigo_siat']
                ]);
                $dataValidated['unidad_medida_id'] = $unidadMedida['id'];
            }

            if (isset($dataValidated['imagen'])) {
                try {
                    $relativePath = $this->insertBase64Image($dataValidated['imagen'], 'productos');
                } catch (\Throwable $th) {
                    $relativePath = null;
                }
                $dataValidated['imagen'] = $relativePath;
            }

            $data = Producto::create($dataValidated);

            $cargaPrecio = CargaPrecio::create($dataValidated['cargaPrecio']);

            PrecioGeneral::create([
                'precio_menor' =>$dataValidated['precio_menor'],
                'precio_mayor' => $dataValidated['precio_mayor'],
                'producto_id' => $data->id,
                'carga_precio_id'=>$cargaPrecio->id,
            ]);

            DB::commit();
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($data, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
//            dd($error->getMessage());
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Producto $producto
     * @return \Illuminate\Http\Response
     */
    public function show(Producto $producto)
    {
        try {
            $message = "Registro recuperado correctamente!!";
            $dataAdicional = $this->SuccessResponse($message);
            return ProductoResource::make($producto)->additional($dataAdicional);
        } catch (\Throwable $error) {
            $message = 'Obteción de datos fallida.';
            return $this->ErrorResponse($message, $error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateProductoRequest $request
     * @param \App\Models\Producto $producto
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        DB::beginTransaction();
        try {
            $dataValidated = $request->validated();

            $unidadMedidaBuscado = UnidadMedida::where('unidad_medida', $dataValidated['unidad_medida'])->first();

            if (!is_null($unidadMedidaBuscado)) {
                $dataValidated['unidad_medida_id'] = $unidadMedidaBuscado['id'];
            } else {
                $unidadMedida = UnidadMedida::create([
                    'unidad_medida' => $dataValidated['unidad_medida'],
                    'valor_catalogo_id' => $dataValidated['codigo_siat']
                ]);
                $dataValidated['unidad_medida_id'] = $unidadMedida['id'];
            }

            if (isset($dataValidated['imagen'])) {
                try {
                    $relativePath = $this->insertBase64Image($dataValidated['imagen'], 'productos', $producto->imagen);
                } catch (\Throwable $th) {
                    $relativePath = null;
                }
                $dataValidated['imagen'] = $relativePath;
            }

            $producto->update($dataValidated);

            $cargaPrecio = CargaPrecio::create($dataValidated['cargaPrecio']);

            $producto->precioGeneral->update([
                'precio_menor' =>$dataValidated['precio_menor'],
                'precio_mayor' => $dataValidated['precio_mayor'],
                'carga_precio_id'=>$cargaPrecio->id,
            ]);


            DB::commit();
            $message = "Se Edito correctamente!!";
            return $this->CreatedResponse($producto, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            dd($error);
            DB::rollBack();
            $message = "Edicion fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Producto $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Producto $producto)
    {
        try {
            if ($producto->estado == 'ACTIVO') {
                $producto->update(['estado' => 'INACTIVO']);
                $message = "Producto ".$producto->id." anulado correctamente!!";
            }else if($producto->estado == 'INACTIVO'){
                $producto->update(['estado' => 'ACTIVO']);
                $message = "Producto ".$producto->id." activado correctamente!!";
            }

            return $this->SuccessResponse($message,Response::HTTP_NO_CONTENT);
//            return $this->SuccessResponse($message);
        } catch (\Throwable $error) {
            $message = "Eliminación fallida";
            return $this->ErrorResponse($message, $error, Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Download excel template to import products
     */
    public function exportTemplate()
    {
        return Excel::download(new TemplateImportProductExport, 'plantilla_importacion_productos.xlsx');
    }

    /**
     * Import products
     */
    public function import(StoreProductoRequest $request,StorePrecioMasivoRequest $requestPrecioMasivo)
    {

        try {
            $expectedHeaders =  [
                'codigo',
                'producto',
                'descripcion',
                'procedencia',
                'unidad_medida',
                'codigo_clasificador_unidad',
//                'codigo_clasificador_producto',
                'precio_menor',
                'precio_mayor'
            ];

            $headings = (new HeadingRowImport)->toArray($request->file('archivo'));
            $productSheetHeader = $headings[0][0];
//            dd($expectedHeaders,$productSheetHeader,array_diff($expectedHeaders,$productSheetHeader));
            if (count(array_intersect($expectedHeaders, $productSheetHeader)) < count($expectedHeaders)) {
                // El archivo importado no tiene todos los encabezados esperados
                $missingHeaders = implode(', ', array_diff($expectedHeaders, $productSheetHeader));
                $message = "El archivo importado no contiene los encabezados esperados. Faltan: $missingHeaders";
                return response()->json([
                    'message' => $message,
                    'errors' => ''
                ], Response::HTTP_PRECONDITION_FAILED);

            }else{
                //creaa el precio
                $validatedCargaPrecio = $requestPrecioMasivo->validated();
                $cargaPrecio = CargaPrecio::create($validatedCargaPrecio);
                $validatedCargaPrecio['carga_precio_id'] = $cargaPrecio->id;

                //import producto
//                $productImport = new ProductosImport();
//                Excel::import($productImport, $request->file('archivo'));
//                $productProcess = new Process(['php', 'artisan', 'import:products', $request->file('archivo')->getPathname()]);
//                $productProcess->start();
//                $productProcess->wait();
                Excel::import(new ProductosImport($validatedCargaPrecio,$validatedCargaPrecio['descripcion']), $request->file('archivo'));
//                Excel::import(new PrecioGeneralImport($validatedCargaPrecio,$validatedCargaPrecio['descripcion']), $request->file('archivo'));

                return $this->SuccessResponse('Se importo correctamente!');
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $error) {
            if ($error->failures()) {
                $errors = array_map(function ($fail) {
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
}
