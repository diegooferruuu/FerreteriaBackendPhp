<?php

namespace App\Http\Services;

use App\Imports\PrecioParticularImport;
use App\Models\PrecioParticular;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CargaPrecioService
{
    /**
     * Get sucursalIds
     *
     * @param array $params
     * @return array
     */
    public function getSucursalIds(array $params): array
    {
        switch ($params['lugar_carga']) {
            case 'sucursal':
                $sucursales = $params['sucursales'];
                break;
            case 'departamento':
                $sucursales = DB::table('departamentos')->whereIntegerInRaw('id', $params['departamentos'])
                    ->join('provincias', 'provincias.departamento_id', 'departamentos.id')
                    ->join('municipios', 'municipios.provincia_id', 'provincias.id_provincia')
                    ->join('localidades', 'localidades.municipio_id', 'municipios.id_municipio')
                    ->join('sucursales', 'sucursales.localidad_id', 'localidades.id_localidad')
                    ->pluck('sucursales.id')
                    ->toArray();
                break;

            case 'provincia':
                $sucursales = DB::table('provincias')->whereIntegerInRaw('id_provincia', $params['provincias'])->join('municipios', 'municipios.provincia_id', 'provincias.id_provincia')
                    ->join('localidades', 'localidades.municipio_id', 'municipios.id_municipio')
                    ->join('sucursales', 'sucursales.localidad_id', 'localidades.id_localidad')
                    ->pluck('sucursales.id')
                    ->toArray();
                break;

            case 'municipio':
                $sucursales = DB::table('municipios')->whereIntegerInRaw('id_municipio', $params['municipios'])->join('localidades', 'localidades.municipio_id', 'municipios.id_municipio')
                    ->join('sucursales', 'sucursales.localidad_id', 'localidades.id_localidad')
                    ->pluck('sucursales.id')
                    ->toArray();
                break;

            case 'localidad':
                $sucursales = DB::table('localidades')->whereIntegerInRaw('id_localidad', $params['localidades'])->join('sucursales', 'sucursales.localidad_id', 'localidades.id_localidad')
                ->pluck('sucursales.id')
                ->toArray();
                break;
            default:
                $sucursales = [];
                break;

        }

        return $sucursales;
    }

    /**
     * Store particular prices
     *
     * @return void
     */
    public function storeParticularPrices(array $params)
    {
        //obtener sucursales
        $sucursalIds = $this->getSucursalIds($params);

        if( count($sucursalIds) == 0 ) {
            throw new \Exception("Ninguna sucursal encontrada para {$params['lugar_carga']}(s)");
        }

        if( request()->route()->named('carga_precios.import') ) {
            Excel::import(new PrecioParticularImport($params), request()->file('archivo'));
        } else {
            PrecioParticular::insert($params['precios']);
        }

        $precioParticularIds = PrecioParticular::select('id_precio_particular', 'producto_id')->where('carga_precio_id', $params['carga_precio_id'])->get()->toArray();

        $joinedPreciosSucursales = Arr::crossJoin($sucursalIds, $precioParticularIds);

        $mappedPreciosSucursales = Arr::map($joinedPreciosSucursales, function($item) {
            return [
                'sucursal_id' => $item[0],
                'producto_id' => $item[1]['producto_id'],
                'precio_particular_id' => $item[1]['id_precio_particular'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        foreach (array_chunk($mappedPreciosSucursales, 1000) as $key => $chunk) {
            DB::table('inventario')->upsert($chunk, ['producto_id', 'sucursal_id'], ['precio_particular_id', 'updated_at']);
        }
    }
}
