<?php

namespace App\Imports;

use App\Imports\Sheets\ClasificacionProductoSheet;
use App\Imports\Sheets\ProductoSheet;
use App\Models\ClasificacionProducto;
use App\Models\Procedencia;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeSheet;

class ProductoImport implements WithMultipleSheets
{
    protected $productSheetHeader;
    protected $clasificacionProductos;
    protected $productos;

    public function __construct(array $productSheetHeader)
    {
        $this->productSheetHeader = $productSheetHeader;

        $this->clasificacionProductos = ClasificacionProducto::get(['id', 'clasificacion']);
        $this->procedencia = Procedencia::get(['id','procedencia']);
        $this->unidadMedida = UnidadMedida::get(['id','unidad_medida']);
        $this->productos = Producto::get(['id', 'producto']);
    }

    public function sheets(): array
    {
        return [

            'Clasificaciones' => new ClasificacionProductoSheet($this->clasificacionProductos),
            'Productos' => new ProductoSheet(
                $this->clasificacionProductos,
                $this->productos,
                $this->productSheetHeader
            ),
        ];
    }


}
