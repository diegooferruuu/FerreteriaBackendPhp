<?php

namespace Database\Seeders;

use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //ITG
        // Sucursal::create([
        //     'codigo_siat' => 0,
        //     'nombres' => 'Casa Matriz',
        //     'abreviatura' => 'CM',
        //     'direccion' => 'Calle Uruguay #545 - Zona Central',
        //     'departamento_id' => '1',
        //     'telefono' => '4259352',
        //     'estado' => 'ACTIVO',
        //     'email' => 'itg@ferreteriaamerica.com'
        // ]);
        //RIVERO
//        Sucursal::create([
//            'codigo_siat' => 0,
//            'nombres' => 'Casa Matriz',
//            'abreviatura' => 'CM',
//            'direccion' => 'Calle Uruguay # S/N - Zona HIPODROMO',
//            'departamento_id' => '1',
//            'telefono' => '4289555',
//            'estado' => 'ACTIVO',
//            'email' => 'ignacio.grf@gmail.com'
//        ]);
        //AMERICA
       Sucursal::create([
           'codigo_siat' => 0,
           'nombres' => 'Casa Matriz',
           'abreviatura' => 'CM',
           'direccion' => 'Calle Uruguay #547 - Zona Sudeste',
           'departamento_id' => '1',
           'telefono' => '4229019 - 4225961',
           'estado' => 'ACTIVO',
           'email' => 'test@test.com'
       ]);
    }
}
