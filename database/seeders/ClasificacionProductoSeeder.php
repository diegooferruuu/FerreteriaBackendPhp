<?php

namespace Database\Seeders;

use App\Models\Procedencia;
use App\Models\UnidadMedida;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClasificacionProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clasificaciones_producto')->insert([
            'clasificacion' => 'Inventariable',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
        DB::table('clasificaciones_producto')->insert([
            'clasificacion' => 'Servicio',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

//        UnidadMedida::create([
//            'unidad_medida' => '1/2 Metros',
//
//        ]);
//        UnidadMedida::create([
//            'unidad_medida' => '1 litro',
//
//        ]);
        Procedencia::create([
            'procedencia' => 'Chile',
        ]);

    }
}
