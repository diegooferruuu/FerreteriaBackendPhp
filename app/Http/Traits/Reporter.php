<?php

namespace App\Http\Traits;

use App\Exports\ReportCollectionExport;
use App\Exports\ReportExport;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

trait Reporter
{
    public function export($params, $custom = false) {
        $formats = [
            'pdf' => \Maatwebsite\Excel\Excel::DOMPDF,
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
        ];
    
        $format = request()->query('format', 'pdf');
        $paperSize = request()->query('paper_size', 'letter');
        $orientation = request()->query('orientation', 'portrait');
        
        $currentDate = now()->format('YmdHi');
        $slugTitle = Str::slug($params['title'], '_');
        $documentName = "{$slugTitle}_{$currentDate}.{$format}";

        if( $format == 'pdf') {
            $pdf = Pdf::loadView('reports.pdfReport', [
                'data' => $params['data'], 
                'title' => $params['title'], 
                'columns' => $params['columns']
            ])
            ->setPaper($paperSize, $orientation);
            return $pdf->stream($documentName);
        }

        return (new ReportCollectionExport($params['data'], $params['columns']))->download($documentName, $formats[$format] ?? $formats['csv']);
    }
}