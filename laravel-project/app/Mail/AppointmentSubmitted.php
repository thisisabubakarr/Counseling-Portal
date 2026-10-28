<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Appointment request submitted');
    }

    public function content(): Content
    {
        $a = $this->appointment;

        return new Content(
            view: 'emails.appointment-event',
            with: [
                'body' => "Your appointment request ({$a->request_code}) is pending for {$a->date->toDateString()} at {$a->slot} with {$a->counselor_name}.",
            ],
        );
    }
}
