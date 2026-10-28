<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment, public string $fromDate, public string $fromSlot)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Appointment rescheduled');
    }

    public function content(): Content
    {
        $a = $this->appointment;

        return new Content(
            view: 'emails.appointment-event',
            with: [
                'body' => "Appointment ({$a->request_code}) was rescheduled from {$this->fromDate} {$this->fromSlot} to {$a->date->toDateString()} {$a->slot}.",
            ],
        );
    }
}
