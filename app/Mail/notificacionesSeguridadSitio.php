<?php

namespace App\Mail;

use App\Models\WbSeguridadSitio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class notificacionesSeguridadSitio extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;
    public $mensaje;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(WbSeguridadSitio $solicitud, $mensaje)
    {
        $this->solicitud = $solicitud;
        $this->mensaje = $mensaje;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Notificaciones Seguridad Sitio',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    /* public function content()
    {
        return new Content(
            view: 'seguridad-sitio\notifiaciones.view.blade.php',
        );
    } */

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }

    public function build()
    {
        return $this->view('seguridad-sitio/notifiaciones');
    }
}