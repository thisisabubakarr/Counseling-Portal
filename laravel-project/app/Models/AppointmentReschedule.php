<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentReschedule extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'from_date',
        'from_slot',
        'to_date',
        'to_slot',
        'by',
        'at',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
