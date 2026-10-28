<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Appointment accepted');
    }

    public function content(): Content
    {
        $a = $this->appointment;

        return new Content(
            view: 'emails.appointment-event',
            with: [
                'body' => "Your appointment ({$a->request_code}) was accepted for {$a->date->toDateString()} at {$a->slot} with {$a->counselor_name}.",
            ],
        );
    }
}
