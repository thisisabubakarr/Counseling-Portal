<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'roll_number',
        'cms_student_id',
        'password',
        'name',
        'dob',
        'phone',
        'department',
        'semester',
        'session_status',
        'suicide_intent',
        'suicide_alert',
        'next_session_date',
        'suggested_next_session_date',
        'next_session_date_status',
        'suggested_by_counselor_id',
        'nature_of_consent',
        'last_attended_session',
        'agreement_accepted',
        'cgpa',
        'presenting_concerns',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'password' => 'hashed',
            'dob' => 'date',
            'next_session_date' => 'date',
            'suggested_next_session_date' => 'date',
            'suicide_intent' => 'boolean',
            'suicide_alert' => 'boolean',
            'agreement_accepted' => 'boolean',
            'presenting_concerns' => 'array',
        ];
    }

    public function suggestedByCounselor()
    {
        return $this->belongsTo(Counselor::class, 'suggested_by_counselor_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function sessionLogs()
    {
        return $this->hasMany(SessionLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function supportGroupRequests()
    {
        return $this->hasMany(SupportGroupRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'to_student_id');
    }
}
