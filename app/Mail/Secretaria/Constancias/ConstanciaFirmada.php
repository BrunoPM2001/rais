<?php

namespace App\Mail\Secretaria\Constancias;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConstanciaFirmada extends Mailable {
  use Queueable, SerializesModels;

  public $nombre;
  public $tipo;
  public $pdf;

  public function __construct($nombre, $tipo, $pdf) {
    $this->nombre = $nombre;
    $this->tipo = $tipo;
    $this->pdf = $pdf;
  }


  public function envelope(): Envelope {
    return new Envelope(
      subject: '[RAIS] Respuesta a solicitud de constancia',
    );
  }

  public function content(): Content {
    return new Content(
      view: 'mail.constancia_firmada',
    );
  }

  public function attachments(): array {
    return [
      Attachment::fromData(fn() => $this->pdf, 'Constancia_firmada.pdf')
        ->withMime('application/pdf'),
    ];
  }
}
