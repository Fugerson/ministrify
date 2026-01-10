<?php

namespace App\Mail;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class SupportTicketReply extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public SupportTicket $ticket,
        public SupportMessage $reply
    ) {}

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            messageId: "ticket-{$this->ticket->id}-reply-{$this->reply->id}@ministrify.app",
            text: [
                'X-Priority' => '3',
                'X-Mailer' => 'Ministrify',
            ],
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Відповідь на звернення: {$this->ticket->subject}",
            replyTo: ['support@ministrify.app'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support-reply',
            text: 'emails.support-reply-text',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
