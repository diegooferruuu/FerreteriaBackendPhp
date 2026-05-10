<?php

namespace Database\Seeders;

use App\Models\Transporte;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AutorizacionSistemaSeeder::class,
            RolSeeder::class,
            PermisoSeeder::class,
            UsuarioSeeder::class,
            PerfilSeeder::class,
            DepartamentoSeeder::class,
            SucursalSeeder::class,
            CafcSeeder::class,
            PuntoVentaSeeder::class,
            ClasificacionProductoSeeder::class,
            AtributoSeeder::class,
            MovimientoSeeder::class,
            CatalogoFacturacionSeeder::class,
            MetodoPagoSeeder::class,

        ]);
    }
}
