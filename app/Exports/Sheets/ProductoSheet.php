<?php

namespace App\Exports\Sheets;

use App\Models\Atributo;
use App\Models\Producto;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductoSheet implements WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $atributos;
    public function __construct()
    {
        $this->atributos = Atributo::pluck('atributo');
    }
    /* public function query()
    {
        return Producto::query();
    } */

    /* public function map($producto): array
    {
        $map = array_merge([
            $producto->producto,
            $producto->codigo_barra,
            $producto->codigo_qr,
            $producto->linea?->linea,
            $producto->sublinea?->sub_linea,
            $producto->grupo?->grupo,
            $producto->tipoProducto?->tipo,
            $producto->clasificacionProducto?->clasificacion,
            $producto->proveedor?->proveedor,
        ], $this->getProductAttibutes($producto));

        return $map;
    } */

    public function headings(): array
    {
        return array_merge([
            'codigo',
            'producto',
            'descripcion',
            'procedencia',
            'unidad_medida',
            'codigo_clasificador_unidad',
            'precio_mayor',
            'precio_menor'
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Productos';
    }




}
