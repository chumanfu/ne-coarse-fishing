<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOwnershipClaimNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $listingKind,
        public string $listingName,
        public User $claimant,
        public ?string $message,
        public string $reviewUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Claim] '.$this->claimant->name.' claimed '.$this->listingName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-ownership-claim',
        );
    }
}
