<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $messageContent;
    public $link;
    public $recipientName;

    /**
     * Create a new message instance.
     */
    public function __construct($title, $messageContent, $link, $recipientName = null)
    {
        $this->title = $title;
        $this->messageContent = $messageContent;
        $this->link = $link;
        $this->recipientName = $recipientName ?? 'Bapak/Ibu';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi SPI: ' . $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
