<?php

namespace App\Mail;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClubClaimInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Club $club,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Claim your club listing on '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.club-claim-invite',
        );
    }
}
