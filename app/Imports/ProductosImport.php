<?php

namespace App\Imports;

use App\Models\PrecioGeneral;
use App\Models\Procedencia;
use App\Models\Producto;
use App\Models\UnidadMedida;
use App\Models\ValorCatalogo;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductosImport implements ToCollection,WithHeadingRow,WithBatchInserts,WithChunkReading,WithValidation
{
    protected $idSincronizacion;
    private $params;
    private $lugarCarga;
    public function __construct (array $params, $lugarCarga)
    {

        //catalogos de unidades
        $this->idSincronizacion = 7;
        $this->params = $params;
        $this->lugarCarga = $lugarCarga;
    }

    /**
    * @param array $rows
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        set_time_limit(-1);
        foreach ($rows as $row) {

            $procedencia = Procedencia::firstOrCreate(['procedencia' => $row['procedencia']]);//   where('procedencia',$row['procedencia'])->first();
            $unidadMedida = UnidadMedida::firstOrNew(['unidad_medida' => $row['unidad_medida']]); //where('unidad_medida',$row['unidad_medida'])->firstOrNew();

            if(!is_null($unidadMedida))
            {
                //es nulo
                $valorCatalogo = ValorCatalogo::where('codigo_clasificador',$row['codigo_clasificador_unidad'])
                    ->where('sincronizacion_catalogo_id',$this->idSincronizacion)
                    ->first(['id']);
                if($valorCatalogo) {
                    $unidadMedida->valor_catalogo_id = $valorCatalogo->id;
                }else{
//                    throw new \Exception("Debe sincronizar catalogos del SIN");
                }
            }
            $unidadMedida->save();
            $data = [
                'producto' => $row['producto'],
                'descripcion' => $row['descripcion'],
                'procedencia_id' => $procedencia->id,
                'unidad_medida_id' => $unidadMedida->id,
            ];
            $producto = Producto::updateOrCreate( ['id' =>trim($row['codigo'])],$data);
            //precios
            $dataPrecio = [
                'producto_id' => $producto->id,
                'carga_precio_id' => $this->params['carga_precio_id'],
                'precio_menor' => $row['precio_menor'],
                'precio_mayor' => $row['precio_mayor'],
            ];
            PrecioGeneral::updateOrCreate(['producto_id' => $producto->id], $dataPrecio);

        }

    }

    public function batchSize(): int
    {
        // TODO: Implement batchSize() method.
        return 500;
    }
    public function chunkSize(): int
    {
        // TODO: Implement chunkSize() method.
        return 500;
    }
    public function rules(): array
    {
        // TODO: Implement rules() method.
        return [
            '*.codigo' => 'bail|required|distinct|string|max:200',
            '*.producto' => 'bail|required|string|max:200',
            '*.descripcion' => 'bail|required|string|max:200',
            '*.codigo_barra' => 'bail|nullable|distinct|string|max:30',
            '*.codigo_qr' => 'bail|nullable|distinct|string|max:30',
            '*.procedencia' => 'bail|required|string|max:30',
            '*.unidad_medida' => 'bail|required|string|max:30',
            '*.codigo_clasificador_unidad' => 'bail|required|integer',
//            '*.codigo_clasificador_producto' => 'bail|required|integer',
        ];
    }
}
