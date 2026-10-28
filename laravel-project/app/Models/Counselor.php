<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Counselor extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'counselor_code',
        'cms_counselor_id',
        'name',
        'email',
        'password',
        'active',
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
            'active' => 'boolean',
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function blockedSlots()
    {
        return $this->hasMany(BlockedSlot::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function reviewedStudentReferrals()
    {
        return $this->hasMany(StudentReferral::class, 'reviewed_by_counselor_id');
    }
}
