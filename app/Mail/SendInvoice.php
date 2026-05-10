<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

class SendInvoice extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $factura;
    public $venta;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($factura,$venta)
    {
        $this->factura = $factura;
        $this->venta = $venta;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'EMISIÓN DE FACTURA COMPRA-VENTA',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'Email.sendInvoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        //$pathPdf = Storage::path('public/pdf/'.$factura->cuf.'.pdf');
        return [
            Attachment::fromPath(Storage::path('private/'.$this->factura->xml))
                ->as($this->factura->cuf.".xml")
                ->withMime('application/xml'),
            Attachment::fromPath(Storage::path('public/pdf/'.$this->factura->cuf.'.pdf'))
                ->as($this->factura->cuf.".pdf")
                ->withMime('application/pdf'),
        ];
    }
}
