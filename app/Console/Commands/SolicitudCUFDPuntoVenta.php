<?php

namespace App\Console\Commands;

use App\Http\Services\CufdService;
use App\Models\Cuis;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SolicitudCUFDPuntoVenta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cufd:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dataCuis = Cuis::where('estado','ACTIVO')->get();

            DB::beginTransaction();
            try {
                foreach ($dataCuis as $cuis)
                {
                    $cufdService = new CufdService();
                    $cufdService->handleStore(['cuis_id' => $cuis->id]);
                }
                DB::commit();
                $this->info("Solicitado con exito");
            } catch (\Throwable $error) {
                DB::rollBack();
                $this->error($error->getMessage());
            }


    }
}
