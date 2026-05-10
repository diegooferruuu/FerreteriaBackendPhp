<?php

namespace App\Imports;

use App\Models\HomologacionProducto;
use App\Models\Producto;
use App\Models\ValorCatalogo;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class HomologacionProductoImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, WithValidation, WithUpserts, WithUpsertColumns
{
    private $catalogoProductos;
    public function __construct()
    {
        $this->catalogoProductos = ValorCatalogo::join('sincronizacion_catalogos', function ($join) {
            $join->on('sincronizacion_catalogos.id', 'valores_catalogo.sincronizacion_catalogo_id')
                ->where('sincronizacion_catalogos.catalogo_facturacion_id', 14);
        })->pluck('valores_catalogo.id', 'codigo_clasificador');
        //->pluck('id', 'codigo_clasificador')
//        dd($this->catalogoProductos);
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new HomologacionProducto([
            'producto_id' => trim($row['codigo']),
            'catalogo_producto_id' => $row['catalogo_producto_id'],
            'codigo_siat' => $row['codigo_sin'],
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
        $data['catalogo_producto_id'] = $this->catalogoProductos[$data['codigo_sin']] ?? null;
        return $data;
    }

    public function rules(): array
    {
        return [
            '*.codigo' => ['bail','required','filled','distinct',
                function ($attribute, $value, $fail) {
                    $clean_value = trim($value); // str_replace(' ', '', $value);
                    if (!Producto::where('id', $clean_value)->exists()) {
                        $fail('El campo ' . $attribute . ' no es un ID de producto válido.');
                    }
                }],
            '*.codigo_sin' => 'bail|required|exists:valores_catalogo,codigo_clasificador',
            '*.catalogo_producto_id' => 'bail|required|exists:valores_catalogo,id',
        ];
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return ['producto_id'];
    }

    /**
     * @return array
     */
    public function upsertColumns()
    {
        return ['catalogo_producto_id', 'codigo_siat'];
    }
}
