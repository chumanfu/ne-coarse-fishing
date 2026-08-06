<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MessageReplyNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public MessageThread $thread,
        public Message $message,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        $prefix = $this->forAdmin ? '[Messages]' : '['.config('app.name').']';

        return new Envelope(
            replyTo: $this->forAdmin
                ? [new Address($this->thread->contact_email, $this->thread->contact_name)]
                : [],
            subject: $prefix.' Re: '.$this->thread->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.message-reply',
        );
    }
}
