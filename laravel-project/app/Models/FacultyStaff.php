<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class FacultyStaff extends Authenticatable
{
    use HasFactory;

    protected $table = 'faculty_staff';

    protected $fillable = [
        'employee_id',
        'cms_employee_id',
        'password',
        'name',
        'designation',
        'department',
        'email',
        'phone',
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
        ];
    }

    public function studentReferrals()
    {
        return $this->hasMany(StudentReferral::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'to_faculty_staff_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function supportGroupRequests()
    {
        return $this->hasMany(SupportGroupRequest::class);
    }

    public function sessionLogs()
    {
        return $this->hasMany(SessionLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
