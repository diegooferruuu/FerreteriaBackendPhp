<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        DB::table('almacenes')->insert([
//            'nombres' => 'Almacen El Alto',
//            'abreviatura' => 'aea',
//            'direccion' => 'Rio Seco #123',
//            'latitud' => '-123123123',
//            'longitud' => '99902322',
//            'telefono' => '223322',
//            'estado' => 'ACTIVO',
//            'localidad_id' => 1,
//            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
//            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
//        ]);
        DB::table('almacenes')->insert([
            'nombres' => 'Almacen La Paz',
            'abreviatura' => 'aea',
            'direccion' => 'Rio Seco #123',
            'latitud' => '-123123123',
            'longitud' => '99902322',
            'telefono' => '223322',
            'estado' => 'ACTIVO',
            'localidad_id' => 1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
}
