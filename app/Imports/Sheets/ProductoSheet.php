<?php

namespace App\Imports\Sheets;

use App\Models\ClasificacionProducto;
use App\Models\Grupo;
use App\Models\Linea;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\SubLinea;
use App\Models\TipoProducto;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductoSheet implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation
{
    private $clasificacionProductos;
    private $atributos;
    private $productos;
    private $header;
    protected static $productAttributes = [];

    public function __construct(
        $clasificacionProductos,
        $atributos,
        $productos,
        $header
    ) {

        $this->clasificacionProductos = $clasificacionProductos;
        $this->atributos = $atributos;
        $this->productos = $productos;
        $this->header = $header;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        if( $this->productos->contains('producto', $row['producto']) ) {
            return null;
        }


        $idProducto = uniqid('p_', true);

        $productAttributes['producto_id'] = $idProducto;
        self::$productAttributes[] = $productAttributes;

        return new Producto([
            'id' => $row['codigo'],
            'producto' => $row['producto'],
            'codigo_barra' => $row['codigo_barra'],
            'codigo_qr' => $row['codigo_qr'],
            'clasificacion_producto_id' => $row['clasificacion_producto_id'],
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
        $data['clasificacion_producto_id'] = $this->getForeignId(['table' => 'clasificaciones_producto', 'results' => 'clasificacionProductos', 'field' => 'clasificacion', 'value' => $data['clasificacion'], 'id' => 'id']);
        return $data;
    }

    public function rules(): array
    {
        return array_merge([
            '*.producto' => 'bail|required|distinct|string|max:200',
            '*.codigo_barra' => 'bail|nullable|distinct|string|max:30',
            '*.codigo_qr' => 'bail|nullable|distinct|string|max:30',
            '*.clasificacion_producto_id' => 'bail|required|integer',
        ]);
    }



    public function getForeignId($model)
    {
        $foreignId = $this->{$model['results']}->where($model['field'], $model['value'])->value($model['id']);
        if( !$foreignId ) {
            $foreignId = DB::table($model['table'])->where($model['field'], $model['value'])->value($model['id']);
        }
        return $foreignId;
    }

}
