<?php

namespace App\Imports;

use App\Models\PrecioGeneral;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpParser\ErrorHandler\Collecting;

class PrecioGeneralImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, WithUpserts
{

    private $params;
    private $lugarCarga;
    public function __construct(array $params, $lugarCarga)
    {
        $this->params = $params;
        $this->lugarCarga = $lugarCarga;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {

        foreach ($rows as $row)
        {
            $data = [
                'producto_id' => $row['codigo'],
                'carga_precio_id' => $row['carga_precio_id']
            ];
            if($this->lugarCarga == 'mayor')
            {
                $data['precio_mayor'] = $row['precio_mayor'];
            }else if($this->lugarCarga== 'menor'){
                $data['precio_menor'] = $row['precio_menor'];
            }
            else {
                $data['precio_menor'] = $row['precio_menor'];
                $data['precio_mayor'] = $row['precio_mayor'];
            }

            PrecioGeneral::updateOrCreate(['producto_id' => $row['codigo']], $data);

        }
    }
    public function uniqueBy()
    {
//        return 'producto_id';
    }
    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function prepareForValidation($data, $index)
    {
        $data['carga_precio_id'] = $this->params['carga_precio_id'];
        return $data;
    }

    public function rules(): array
    {
        return [
            '*.codigo' => 'bail|required|filled|distinct',
            '*.precio_menor' => 'bail|numeric|min:0',
            '*.precio_mayor' => 'bail|numeric|min:0',
        ];
    }
}
