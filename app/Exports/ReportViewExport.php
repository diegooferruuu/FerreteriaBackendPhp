<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class ReportViewExport extends ReportExport implements FromView
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

    public function view(): View
    {
        return view('reports.kardexTable', [
            'data' => $this->data,
        ]);
    }
}
