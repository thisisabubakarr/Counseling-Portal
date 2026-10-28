<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'notification_code',
        'to_role',
        'to_student_id',
        'to_counselor_id',
        'to_faculty_staff_id',
        'title',
        'message',
        'type',
        'read_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'to_student_id' => 'integer',
            'to_counselor_id' => 'integer',
            'to_faculty_staff_id' => 'integer',
            'read_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'to_student_id');
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class, 'to_counselor_id');
    }

    public function facultyStaff()
    {
        return $this->belongsTo(FacultyStaff::class, 'to_faculty_staff_id');
    }
}
