<?php

namespace App\Console\Commands;

use App\Http\Services\SincronizacionCatalogoService;
use App\Models\Sucursal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SolicitudSincronizacionCatalogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:solicitud-sincronizacion-catalogo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        DB::beginTransaction();
        try {
            $dataSucursal = Sucursal::where('estado','ACTIVO')->first();
            $syncable_type = 'sucursal';


            SincronizacionCatalogoService::syncAll([
                'syncable_type' => $syncable_type,
                'syncable_id' => $dataSucursal->id
            ]);

            DB::commit();
            $this->info("Solicitado con exito");
        } catch (\Throwable $error) {
            DB::rollBack();
            $this->error($error->getMessage());
        }



    }
}
