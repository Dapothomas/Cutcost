<?php

namespace App\Mail;

use App\Models\WaitlistSignup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistJoinedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WaitlistSignup $signup) {}

    public function envelope(): Envelope
    {
        $who = $this->signup->shop_name
            ?: ($this->signup->name ?: $this->signup->email);

        return new Envelope(
            subject: "Cutcost waitlist: {$who}",
            replyTo: [$this->signup->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.waitlist-joined',
        );
    }
}
