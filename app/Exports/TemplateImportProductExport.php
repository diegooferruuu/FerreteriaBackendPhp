<?php

namespace App\Exports;

use App\Exports\Sheets\ClasificacionProductoSheet;
use App\Exports\Sheets\ProductoSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateImportProductExport implements WithMultipleSheets
{
    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new ProductoSheet,
            new ClasificacionProductoSheet,
        ];
    }
}
