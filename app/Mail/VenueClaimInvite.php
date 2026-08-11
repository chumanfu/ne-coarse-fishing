<?php

namespace App\Mail;

use App\Models\Venue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VenueClaimInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Venue $venue,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Claim your venue listing on '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.venue-claim-invite',
        );
    }
}
