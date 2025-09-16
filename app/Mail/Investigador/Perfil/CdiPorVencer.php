<?php

namespace App\Mail\Investigador\Perfil;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CdiPorVencer extends Mailable {
  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   */
  public function __construct(public $investigador) {
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope {
    return new Envelope(
      subject: 'URGENTE - CDI POR VENCER',
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content {
    return new Content(
      view: 'mail.cdi_por_vencer',

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
