<?php

namespace Database\Seeders;

use App\Models\InventarioMovimiento;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventarioMovimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('inventario_movimiento')->insert([
            'inicial' => 100,
            'ingresos' => null,
            'egresos' => null,
            'precio' => '10.00',
            'identificador' => '1',
            'origen' => 'inventario',
            'fecha' => now(),
            'observaciones' => fake()->word(),
            'movimiento_id' => 1,
            'inventario_id' => 1,
        ]);

        InventarioMovimiento::factory()->count(500)->create();
    }
}
