<?php

namespace App\Mail;

use App\Models\TackleShop;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TackleShopClaimInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TackleShop $tackleShop,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Claim your tackle shop listing on '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tackle-shop-claim-invite',
        );
    }
}
