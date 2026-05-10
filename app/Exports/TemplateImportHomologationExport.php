<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateImportHomologationExport implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Producto::query()->doesntHave('homologacion');
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
            'codigo',
            'Descripcion',
            'Codigo SIN',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]]
        ];
    }
}
