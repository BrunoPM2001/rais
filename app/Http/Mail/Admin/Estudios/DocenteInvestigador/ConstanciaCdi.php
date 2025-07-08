<?php

namespace App\Mail\Admin\Estudios\DocenteInvestigador;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConstanciaCdi extends Mailable {
  use Queueable, SerializesModels;

  public $nombre;
  public $pdf;

  public function __construct($nombre, $pdf) {
    $this->nombre = $nombre;
    $this->pdf = $pdf;
  }


  public function envelope(): Envelope {
    return new Envelope(
      subject: 'CONSTANCIA DE DOCENTE INVESTIGADOR',
    );
  }

  public function content(): Content {
    return new Content(
      view: 'mail.cdi',
    );
  }

  public function attachments(): array {
    return [
      Attachment::fromData(fn() => $this->pdf, 'CDI.pdf')
        ->withMime('application/pdf'),
    ];
  }
}
