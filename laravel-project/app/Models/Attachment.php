<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'attachment_code',
        'student_id',
        'faculty_staff_id',
        'file_name',
        'mime_type',
        'url',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
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
