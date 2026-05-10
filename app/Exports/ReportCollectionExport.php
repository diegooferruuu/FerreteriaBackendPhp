<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReportCollectionExport extends ReportExport implements FromCollection
{
    use Exportable;

    protected $data;
    protected $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
        parent::__construct($this->data, $this->columns);
    }

    public function collection()
    {
        return $this->data;
    }
}
