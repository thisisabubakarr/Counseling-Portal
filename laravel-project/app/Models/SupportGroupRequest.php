<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportGroupRequest extends Model
{
    protected $table = 'support_group_requests';

    protected $fillable = [
        'request_code',
        'student_id',
        'student_name',
        'roll_number',
        'faculty_staff_id',
        'faculty_staff_name',
        'employee_id',
        'reason',
        'topics',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
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

    public function owner(): Student|FacultyStaff|null
    {
        return $this->faculty_staff_id ? $this->facultyStaff : $this->student;
    }
}
