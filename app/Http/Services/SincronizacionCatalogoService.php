<?php

namespace App\Http\Services;

use App\Http\Services\Siat\DataSync;
use App\Models\AutorizacionSistema;
use App\Models\CatalogoFacturacion;
use App\Models\MetodoPago;
use App\Models\SincronizacionCatalogo;
use App\Models\Sucursal;
use App\Models\ValorCatalogo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Gestion de sincronizacionCatalogo de catalogos.
 */
class SincronizacionCatalogoService
{
    /**
     * Sincronizar catalogo.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return void
     */
    public static function sync($params)
    {
        $sincronizacionCatalogo = SincronizacionCatalogo::updateOrCreate($params, ['updated_at' => now()]);
        if( !$sincronizacionCatalogo->syncable->cuis ) {
            throw new \Exception("Establece un CUIS para la sucursal o punto de venta");
        }
        $cuis = $sincronizacionCatalogo->syncable->cuis->valor;

        $catalogMethod = $sincronizacionCatalogo->catalogo->metodo;

        if($sincronizacionCatalogo->syncable_type == 'sucursal') {
            $branchCode = $sincronizacionCatalogo->syncable->codigo_siat;
            $posCode = 0;
        } else {
            $branchCode = $sincronizacionCatalogo->syncable->sucursal->codigo_siat;
            $posCode = $sincronizacionCatalogo->syncable->codigo_siat;
        }

        $serviceDataSync = new DataSync();

        $response = $serviceDataSync->syncCatalog($catalogMethod, [
            'cuis' => $cuis,
            'branch_code' => $branchCode,
            'pos_code' => $posCode,
        ]);
//        dd($response);
        //consultamos si la respuesta es array
        if(is_array($response['data']))
        {
            $correlativoLeyenda = 0;
            $mapped = Arr::map($response['data'], function ($item, $index) use ($sincronizacionCatalogo,$correlativoLeyenda) {
                $correlativo = $index + 1;
                // Existe codigo_clasificador duplicados por cada catalogo
                if ($sincronizacionCatalogo->catalogo_facturacion_id != 3)
                {
                    return [
                        'codigo_clasificador' => $item->codigoClasificador ?? $item->codigoCaeb ?? $item->codigoDocumentoSector ?? $item->codigoProducto ?? $item->codigoActividad,
                        'codigo_actividad' => $item->codigoActividad ?? null,
                        'descripcion' => $item->descripcion ?? $item->descripcionLeyenda ?? $item->tipoDocumentoSector ?? $item->descripcionProducto,
                        'sincronizacion_catalogo_id' => $sincronizacionCatalogo->id,
                    ];
                }else{
//                    $correlativoLeyenda++;
                    return [
                        'codigo_clasificador' => $correlativo,
                        'codigo_actividad' => $item->codigoActividad ?? null,
                        'descripcion' => $item->descripcion ?? $item->descripcionLeyenda ?? $item->tipoDocumentoSector ?? $item->descripcionProducto,
                        'sincronizacion_catalogo_id' => $sincronizacionCatalogo->id,
                    ];
                }

            });

        } else{
            $mapped = [[
                'codigo_clasificador' => $response['data']->tipoActividad,
                'codigo_actividad' => $response['data']->codigoCaeb,
                'descripcion' => $response['data']->descripcion,
                'sincronizacion_catalogo_id' => $sincronizacionCatalogo->id,
            ]];
        }
//        dd($mapped);
        $catalogs = collect($mapped);


        $catalogs = $catalogs->unique(function ($item) {
            return $item['codigo_clasificador'].$item['sincronizacion_catalogo_id'];
        });


        $catalogs = $catalogs->values()->all();

        ValorCatalogo::upsert($catalogs, ['codigo_clasificador', 'sincronizacion_catalogo_id'], ['descripcion']);

        // Insertando/actualizando metodos_pago si la sucursal es la casa matriz y el catalogo es metodos de pago
        if( $branchCode == 0 && $sincronizacionCatalogo->catalogo->id == 11 )
        {
            $mapped = Arr::map($catalogs, function ($item) {
                return [
                    'metodo' => $item['descripcion'],
                    'codigo_metodo_pago_siat' => $item['codigo_clasificador'],
                ];
            });
            MetodoPago::upsert($mapped, ['codigo_metodo_pago_siat'], ['metodo']);
        }

    }

    /**
     * Sincronizar todos los catalogos.
     *
     * @param array $params     Argumentos necesarios para llamar a la funcion.
     * @return void
     */
    public static function syncAll($params)
    {
        $catalogos = CatalogoFacturacion::where('estado', 'ACTIVO')->pluck('metodo', 'id')->toArray();

        $syncParams = Arr::map($catalogos, function($catalogo, $key) use ($params) {
            return [
                'syncable_type' => $params['syncable_type'],
                'syncable_id' => $params['syncable_id'],
                'catalogo_facturacion_id' => $key,
            ];
        });

        foreach ($syncParams as $key => $item) {
            self::sync($item);
        }
    }
}
