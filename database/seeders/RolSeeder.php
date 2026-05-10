<?php

namespace Database\Seeders;

use App\Models\Rol;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Rol::create([
            'rol' => 'Administrador',
            'slug' => 'administrador',
            'descripcion' => 'Usuario con acceso total.',
        ]);
        Rol::create([
            'rol' => 'Invitado',
            'slug' => 'invitado',
            'descripcion' => 'Usuario invitado.',
        ]);
        Rol::create([
            'rol' => 'Ventas',
            'slug' => 'ventas',
            'descripcion' => 'Usuario con permisos ventas.',
        ]);
        Rol::create([
            'rol' => 'Proformas',
            'slug' => 'proformas',
            'descripcion' => 'Usuario con permisos proformas.',
        ]);
    }
}
