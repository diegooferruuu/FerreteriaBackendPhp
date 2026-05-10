<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('metodos_pago')->insert([
            ['metodo' => 'Efectivo', 'codigo_metodo_pago_siat' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['metodo' => 'Tarjeta', 'codigo_metodo_pago_siat' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
