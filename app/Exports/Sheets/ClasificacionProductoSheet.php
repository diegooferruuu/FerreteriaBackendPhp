<?php

namespace App\Exports\Sheets;

use App\Models\ClasificacionProducto;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClasificacionProductoSheet implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function query()
    {
        return ClasificacionProducto::query();
    }

    public function map($clasificacionProducto): array
    {
        return [
            $clasificacionProducto->clasificacion,
        ];
    }

    public function headings(): array
    {
        return [
            'Clasificacion',
        ];
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
        return 'Clasificaciones';
    }
}
