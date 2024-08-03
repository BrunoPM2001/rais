<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SampleMailable extends Mailable {
  use Queueable, SerializesModels;

  public $pdf;

  /**
   * Create a new message instance.
   */
  public function __construct($pdf) {
    $this->pdf = $pdf;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope {
    return new Envelope(
      subject: 'Ejemplo de asunto',
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content {
    return new Content(
      view: 'mail.ejemplo',
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array {
    return [
      Attachment::fromData(fn () => $this->pdf, 'Constancia.pdf')
        ->withMime('application/pdf'),
    ];
  }
}
