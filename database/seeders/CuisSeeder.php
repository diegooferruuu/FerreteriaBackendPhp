<?php

namespace Database\Seeders;

use App\Models\Cuis;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CuisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cuis::factory()
        ->count(2)
        ->state(new Sequence(
            ['sucursal_id' => 1],
            ['punto_venta_id' => 1],
        ))
        ->create();
    }
}
