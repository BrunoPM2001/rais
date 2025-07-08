<?php

namespace App\Mail\Investigador\Constancias;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConstanciaParaFirma extends Mailable {
  use Queueable, SerializesModels;

  public $nombre;
  public $email;
  public $pdf;

  public function __construct($nombre, $email, $pdf) {
    $this->nombre = $nombre;
    $this->email = $email;
    $this->pdf = $pdf;
  }


  public function envelope(): Envelope {
    return new Envelope(
      subject: '[RAIS] Solicitud de constancia',
    );
  }

  public function content(): Content {
    return new Content(
      view: 'mail.constancia_para_firma',
    );
  }

  public function attachments(): array {
    return [
      Attachment::fromData(fn() => $this->pdf, 'Constancia.pdf')
        ->withMime('application/pdf'),
    ];
  }
}
