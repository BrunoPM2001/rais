<?php

namespace App\Mail\Investigador\Publicaciones;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitarSerAutor extends Mailable {
  use Queueable, SerializesModels;

  public $publicacion;
  public $investigador;

  /**
   * Create a new message instance.
   */
  public function __construct($publicacion, $investigador) {
    $this->publicacion = $publicacion;
    $this->investigador = $investigador;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope {
    return new Envelope(
      subject: '[RAIS] Solicitud de inclusión a publicación',
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content {
    return new Content(
      view: 'mail.incluir_publicacion',

    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array {
    return [];
  }
}
