<?php

namespace App\Exports;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements WithStyles, WithHeadings, WithMapping, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    use Exportable;

    protected $data;
    protected $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function headings(): array
    {
        return Arr::map($this->columns, function($column) {
            return isset($column['as']) ? $column['as'] : Str::of($column['field'])->headline()->upper();
        });
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->columns as $column) {
            $mapped[] = $row->{$column['field']};
        }
        return $mapped;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila
            1 => ['font' => ['bold' => true]]
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function(BeforeWriting $event) {
                $event->getWriter()->getDelegate()->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $event->getWriter()->getDelegate()->getActiveSheet()->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
            }
        ];
    }
}
