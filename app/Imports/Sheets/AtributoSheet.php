<?php

namespace App\Imports\Sheets;

use App\Models\Atributo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AtributoSheet implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{

    private $atributos;
    public function __construct($atributos)
    {
        $this->atributos = $atributos;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if( $this->atributos->contains('atributo', $row['atributo']) ) {
            return null;
        }
        
        return new Atributo([
            'atributo' => $row['atributo'],
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
            '*.atributo' => 'bail|required|distinct|string|max:150',
        ];
    }
}
