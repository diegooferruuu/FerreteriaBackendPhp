<?php

namespace App\Imports\Sheets;

use App\Models\ClasificacionProducto;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClasificacionProductoSheet implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{

    private $clasificacionProductos;
    public function __construct($clasificacionProductos)
    {
        $this->clasificacionProductos = $clasificacionProductos;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if( $this->clasificacionProductos->contains('clasificacion', $row['clasificacion']) ) {
            return null;
        }
        
        return new ClasificacionProducto([
            'clasificacion' => $row['clasificacion'],
        ]);
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function rules(): array
    {
        return [
            '*.clasificacion' => 'bail|required|distinct|string|max:150',
        ];
    }
}
