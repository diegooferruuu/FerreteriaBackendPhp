<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SincronizacionCatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sincronizacion_catalogos')->insert([
            ['id' => 1,'catalogo_facturacion_id' => 1,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 2,'catalogo_facturacion_id' => 2,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 3,'catalogo_facturacion_id' => 3,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 4,'catalogo_facturacion_id' => 4,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 5,'catalogo_facturacion_id' => 5,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 6,'catalogo_facturacion_id' => 6,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 7,'catalogo_facturacion_id' => 7,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 8,'catalogo_facturacion_id' => 8,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 9,'catalogo_facturacion_id' => 9,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 10,'catalogo_facturacion_id' => 10,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 11,'catalogo_facturacion_id' => 11,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 12,'catalogo_facturacion_id' => 12,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 13,'catalogo_facturacion_id' => 13,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 14,'catalogo_facturacion_id' => 14,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 15,'catalogo_facturacion_id' => 15,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 16,'catalogo_facturacion_id' => 16,'syncable_id' => 1,'syncable_type' => 'sucursal'],
            ['id' => 17,'catalogo_facturacion_id' => 17,'syncable_id' => 1,'syncable_type' => 'sucursal'],
        ]);
    }
}
