<?php

namespace App\Imports;

use App\Models\PrecioParticular;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;

class PrecioParticularImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{

    private $params;
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new PrecioParticular([
            'precio_venta' => $row['precio_venta'],
            'descuento_venta' => $row['descuento_venta'],
            'precio_compra' => $row['precio_compra'],
            'descuento_compra' => $row['descuento_compra'],
            'producto_id' => $row['producto_id'],
            'carga_precio_id' => $row['carga_precio_id'],
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

    public function prepareForValidation($data, $index)
    {
        $data['carga_precio_id'] = $this->params['carga_precio_id'];
        return $data;
    }

    public function rules(): array
    {
        return [
            '*.producto_id' => 'bail|required|filled|distinct|exists:productos,id',
            '*.precio_venta' => 'bail|required|numeric|gt:0',
            '*.descuento_venta' => 'bail|required|numeric|gte:0',
            '*.precio_compra' => 'bail|required|numeric|gt:0',
            '*.descuento_compra' => 'bail|required|numeric|gte:0',
        ];
    }
}
