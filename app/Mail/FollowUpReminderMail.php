<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $followUp;
    public $type; // 'initial', 'reminder', 'urgent', 'overdue'
    public $daysRemaining;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\FollowUp $followUp, $type = 'reminder', $daysRemaining = null)
    {
        $this->followUp = $followUp;
        $this->type = $type;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = match($this->type) {
            'initial' => '📋 Notifikasi Tindak Lanjut',
            'overdue' => '🔴 TERLAMBAT - Tindak Lanjut',
            'urgent'  => '🟠 MENDESAK - Tindak Lanjut',
            default   => '🔔 Reminder Tindak Lanjut',
        };

        return new Envelope(
            subject: $prefix . ': ' . $this->followUp->document->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.followup-reminder',
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
