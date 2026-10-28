<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
        'rescheduled' => 'Rescheduled / Confirmed',
        'reschedule_pending_counselor' => 'Reschedule Requested (Pending Counselor Approval)',
        'reschedule_pending_user' => 'Reschedule Requested (Pending User Approval)',
    ];

    protected $fillable = [
        'request_code',
        'student_id',
        'student_name',
        'roll_number',
        'faculty_staff_id',
        'faculty_staff_name',
        'employee_id',
        'counselor_id',
        'counselor_name',
        'date',
        'slot',
        'pending_reschedule_date',
        'pending_reschedule_slot',
        'reschedule_requested_by',
        'status_before_reschedule',
        'status',
        'suicidal_thoughts',
        'mental_health_questions',
        'presenting_concerns',
        'support_group_topics',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'counsellor_notes',
    ];

    protected function casts(): array
    {
        return [
            'student_id' => 'integer',
            'faculty_staff_id' => 'integer',
            'counselor_id' => 'integer',
            'date' => 'date',
            'pending_reschedule_date' => 'date',
            'suicidal_thoughts' => 'boolean',
            'presenting_concerns' => 'array',
            'support_group_topics' => 'array',
        ];
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function facultyStaff()
    {
        return $this->belongsTo(FacultyStaff::class);
    }

    /**
     * Whoever this appointment actually belongs to — a Student or a
     * FacultyStaff, whichever FK is set. Never both.
     */
    public function owner(): Student|FacultyStaff|null
    {
        return $this->faculty_staff_id ? $this->facultyStaff : $this->student;
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class);
    }

    public function rescheduleHistory()
    {
        return $this->hasMany(AppointmentReschedule::class)->orderBy('at');
    }
}
