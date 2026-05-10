<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterSheet;

class VentasExport implements FromCollection, WithHeadings, WithStyles,ShouldAutoSize,WithStrictNullComparison, WithEvents
{
    use RegistersEventListeners;

    protected $ventas;
    protected $fecha_inicio;
    protected $fecha_fin;
    public static $total;
    public static $descuentoTotal;

    public function __construct($ventas,$fecha_inicio, $fecha_fin, $total, $descuentoTotal)
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->ventas = $ventas;
        self::$total = $total;
        self::$descuentoTotal = $descuentoTotal;
        $this->mensajeFecha = 'del ' . Carbon::parse($this->fecha_inicio)->format('d-m-Y') . ' al ' . Carbon::parse($this->fecha_fin)->format('d-m-Y');

    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->ventas;
    }
    public function headings(): array
    {
        return [
            ['REPORTE VENTAS'],
            [$this->mensajeFecha],
            ['Código', 'Cantidad vendida', 'Producto', 'Descripción', 'Procedencia', 'Unidad medida', 'Código clasificador unidad','Código clasificador producto','Precio','Descuento','Precio - descuento', 'Total Bs'],
//            [' ', ' ', ' ', ' ', ' ', ' ', ' ',' ',' ',' ','Total Bs ', $this->total]
        ];

    }
    public function styles(Worksheet $sheet)
    {

        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2')->getFont()->setBold(true);


        $sheet->getStyle('A3:L3')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center']
        ]);
    }
    //adicionar roles
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet'],
        ];
    }
    public static function afterSheet(AfterSheet $event)
    {
        $lastRow = $event->sheet->getHighestRow();

        $event->sheet->setCellValue("K" . ($lastRow + 1), 'Sub Total Bs');
        $event->sheet->setCellValue("L" . ($lastRow + 1), self::$total);

        $event->sheet->setCellValue("K" . ($lastRow + 2), 'Descuento Bs');
        $event->sheet->setCellValue("L" . ($lastRow + 2), self::$descuentoTotal);

        $event->sheet->setCellValue("K" . ($lastRow + 3), 'Total Bs');
        $event->sheet->setCellValue("L" . ($lastRow + 3), self::$total-self::$descuentoTotal);

        // Aplicar negrita
        $boldStyleArray = [
            'font' => [
                'bold' => true,
            ],
        ];

        $event->sheet->getStyle("K" . ($lastRow + 1))->applyFromArray($boldStyleArray);
//        $event->sheet->getStyle("L" . ($lastRow + 1))->applyFromArray($boldStyleArray);

        $event->sheet->getStyle("K" . ($lastRow + 2))->applyFromArray($boldStyleArray);
//        $event->sheet->getStyle("L" . ($lastRow + 2))->applyFromArray($boldStyleArray);

        $event->sheet->getStyle("K" . ($lastRow + 3))->applyFromArray($boldStyleArray);
//        $event->sheet->getStyle("L" . ($lastRow + 3))->applyFromArray($boldStyleArray);
    }

}

