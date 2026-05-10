<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateImportPricesExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Producto::query();
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->descripcion,
        ];
    }

    public function headings(): array
    {
        return [
            'Producto ID',
            'Descripcion',
            'Precio menor',
            'Precio mayor',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila
            1 => ['font' => ['bold' => true]]
        ];
    }
}
