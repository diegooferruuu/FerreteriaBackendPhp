<?php

namespace App\Exports\Sheets;

use App\Models\Atributo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AtributoSheet implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function query()
    {
        return Atributo::query();
    }

    public function map($atributo): array
    {
        return [
            $atributo->atributo,
        ];
    }

    public function headings(): array
    {
        return [
            'Atributo',
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
        return 'Atributos';
    }
}
