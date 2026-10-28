<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    protected $fillable = [
        'session_code',
        'student_id',
        'faculty_staff_id',
        'date',
        'time',
        'notes',
        'status',
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'started_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function facultyStaff()
    {
        return $this->belongsTo(FacultyStaff::class);
    }
}
