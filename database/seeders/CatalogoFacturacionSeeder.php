<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoFacturacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('catalogos_facturacion')->insert([
            ['id' => 1, 'nombre' => 'MotivoAnulacion', 'metodo' => 'sincronizarParametricaMotivoAnulacion', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nombre' => 'Actividades', 'metodo' => 'sincronizarActividades', 'created_at' => now(), 'updated_at' => now()],
            // ['nombre' => 'FechaHora', 'metodo' => 'sincronizarFechaHora', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nombre' => 'LeyendasFactura', 'metodo' => 'sincronizarListaLeyendasFactura', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nombre' => 'TipoHabitacion', 'metodo' => 'sincronizarParametricaTipoHabitacion', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nombre' => 'ActividadesDocumentoSector', 'metodo' => 'sincronizarListaActividadesDocumentoSector', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nombre' => 'TipoDocumentoIdentidad', 'metodo' => 'sincronizarParametricaTipoDocumentoIdentidad', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nombre' => 'UnidadMedida', 'metodo' => 'sincronizarParametricaUnidadMedida', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'nombre' => 'TipoDocumentoSector', 'metodo' => 'sincronizarParametricaTipoDocumentoSector', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'nombre' => 'TiposFactura', 'metodo' => 'sincronizarParametricaTiposFactura', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'nombre' => 'MensajesServicios', 'metodo' => 'sincronizarListaMensajesServicios', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'nombre' => 'TipoMetodoPago', 'metodo' => 'sincronizarParametricaTipoMetodoPago', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'nombre' => 'EventosSignificativos', 'metodo' => 'sincronizarParametricaEventosSignificativos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'nombre' => 'TipoPuntoVenta', 'metodo' => 'sincronizarParametricaTipoPuntoVenta', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'nombre' => 'ProductosServicios', 'metodo' => 'sincronizarListaProductosServicios', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'nombre' => 'TipoEmision', 'metodo' => 'sincronizarParametricaTipoEmision', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'nombre' => 'PaisOrigen', 'metodo' => 'sincronizarParametricaPaisOrigen', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'nombre' => 'TipoMoneda', 'metodo' => 'sincronizarParametricaTipoMoneda', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
