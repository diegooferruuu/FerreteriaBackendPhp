<?php

namespace Database\Seeders;

use App\Models\PuntoVenta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PuntoVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PuntoVenta::create([
            'codigo_siat' => 0,
            'nombre' => "Casa Matriz",
            'descripcion' => "Punto Venta Casa Matriz",
            'sucursal_id' => 1,

        ]);
    }
}
