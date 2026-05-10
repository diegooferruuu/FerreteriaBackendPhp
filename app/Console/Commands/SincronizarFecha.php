<?php

namespace App\Console\Commands;

use App\Http\Services\Siat\DataSync;
use App\Models\PuntoVenta;
use App\Models\Sucursal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SincronizarFecha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sincronizar-fecha';

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
        try {
            //pos, /id
            $dataSucursal = Sucursal::where('estado','ACTIVO')->first();
            $syncable_type = 'sucursal';

            $params = ['cuis' => $dataSucursal->cuis->valor, 'branch_code' => 0, 'pos_code' => 0];

            $serviceDataSync = new DataSync();
            $response = $serviceDataSync->syncDateTime($params);
            if($response->RespuestaFechaHora->transaccion)
            {
                $fecha = $response->RespuestaFechaHora->fechaHora;
                $fechaFormat = Carbon::parse($fecha)->format('Y-m-d H:i:s');
                exec("echo 'P4ssw0rd' | sudo -S date --set '{$fechaFormat}'");
            }
            $this->info("Solicitado con exito");
        } catch (\Throwable $error) {
//            throw $error;
            $this->error($error->getMessage());
        }
    }
}
