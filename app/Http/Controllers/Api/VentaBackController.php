<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArrayDetalleVentaRequest;
use App\Http\Requests\StoreVentaBackRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Http\Services\Siat\SalePurchaseInvoice;
use App\Http\Services\Siat\XmlValidator;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\Siat\Cuf;
use App\Http\Traits\Siat\XmlFile;
use App\Models\Venta;
use App\Http\Helpers\XMLSecLibs\SignedXml;
use App\Http\Services\FacturaService;
use App\Http\Traits\Siat\CompressFile;
use App\Http\Traits\Siat\Hasher;
use DateTime;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VentaBackController extends Controller
{
    use ApiResponser, Cuf, XmlFile, CompressFile, Hasher;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreVentaBackRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVentaBackRequest $ventaRequest, StoreArrayDetalleVentaRequest $itemsRequest)
    {
        DB::beginTransaction();
        try {
            $ventaValidated = $ventaRequest->validated();
            $itemsValidated = $itemsRequest->validated();

            $ventaValidated['codigo_secuencia'] = 1;
            $venta = Venta::create($ventaValidated);

            foreach ($itemsValidated['items'] as $item) {
                $venta->inventarios()->attach($item['inventario_id'], [
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'descuento' => $item['descuento'],
                    'sub_total' => $item['sub_total']
                ]);
            }

            $facturaService = new FacturaService();
            $facturaService->register($venta);

            DB::commit();
            $message = "Se registro correctamente!!";
            return $this->CreatedResponse($venta, $message, Response::HTTP_CREATED);
        } catch (\Throwable $error) {
            DB::rollBack();
            $message = "Registro fallido";
            return $this->ErrorResponse($message, $error->getMessage(), Response::HTTP_PRECONDITION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function show(Venta $venta)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVentaRequest  $request
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVentaRequest $request, Venta $venta)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Venta  $venta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Venta $venta)
    {
        //
    }
}
