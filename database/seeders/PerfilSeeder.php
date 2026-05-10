<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Perfil::create([
            'nombres' => 'Administrador',
            'apellidos' => 'Sistemas',
            'telefono' => 77777777,
            'celular' => 7777777,
            'foto' => 'Sin foto',
            'usuario_id' => 1,
        ]);

//        DB::table('perfiles')->insert([
//            'id' => 2,
//            'nombres' => 'Juana',
//            'apellidos' => 'Diaz',
//            'telefono' => 4889789,
//            'celular' => 789789,
//            'foto' => 'Sin foto',
//            'usuario_id' => 2,
//            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
//            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
//        ]);
    }
}
