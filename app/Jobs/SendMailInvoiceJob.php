<?php

namespace App\Jobs;

use App\Mail\SendInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMailInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $factura;
    public $venta;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($factura, $venta)
    {
        $this->factura = $factura;
        $this->venta = $venta;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */


    public function handle()
    {
        $this->sendMailInvoice($this->factura, $this->venta);
    }

    public function sendMailInvoice($factura,$venta)
    {
        Mail::to($venta->cliente->email)->queue(new SendInvoice($factura,$venta));
    }
}
