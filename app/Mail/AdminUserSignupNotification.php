<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminUserSignupNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $reviewUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Signup] '.$this->user->name.' joined '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-user-signup',
        );
    }
}
