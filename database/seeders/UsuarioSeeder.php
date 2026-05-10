<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\TipoImpresion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usuario = Usuario::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'superAdmin' => true,
            'email' => 'admin@test.com',
            'resent' => 0,
            'estado' => 'ACTIVO',
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => 1,
            'rol_id' => 1
        ]);
        $impresionPagina = TipoImpresion::where('tipo','pagina')->first();

        $usuario->tipoImpresion()->attach($impresionPagina->id);

        /* PERMISOS */
        $permisos = Permiso::all();
        $rol = Rol::where('id',1)->first();
        $rol->permisos()->sync($permisos->pluck('id'));

        /*asignacion de permisos a rol venta */
        $rol = Rol::where('id',3)->first();
        $rol->permisos()->sync([16,17,29,38,41,49,50,52]);

        //asignar permisos a proformas
        $rol = Rol::where('id',4)->first();
        $rol->permisos()->sync([16,43,44,45,46,47]);



    }
}
