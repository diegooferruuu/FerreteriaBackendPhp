<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('movimientos')->insert([
            'movimiento' => 'Inventario inicial',
            'abreviatura' => 'ii',
            'tipo' => 'INICIAL',
            'estado' => 'ACTIVO',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        DB::table('movimientos')->insert([
            'movimiento' => 'Ingreso de mercaderia',
            'abreviatura' => 'im',
            'tipo' => 'INGRESO',
            'estado' => 'ACTIVO',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        DB::table('movimientos')->insert([
            'movimiento' => 'Salida de mercaderia',
            'abreviatura' => 'sm',
            'tipo' => 'EGRESO',
            'estado' => 'ACTIVO',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        DB::table('movimientos')->insert([
            'movimiento' => 'SALIDA POR VENTAS',
            'abreviatura' => 'SPV',
            'tipo' => 'EGRESO',
            'estado' => 'ACTIVO',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
}
