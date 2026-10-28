<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A faculty/staff member referring a STUDENT to counseling. `student_id` is
 * nullable — see migration comment for why.
 */
class StudentReferral extends Model
{
    protected $table = 'student_referrals';

    protected $fillable = [
        'referral_code',
        'faculty_staff_id',
        'referred_by_name',
        'referred_by_designation',
        'referred_by_department',
        'student_id',
        'student_name',
        'student_roll_number',
        'reason',
        'status',
        'counselor_notes',
        'reviewed_by_counselor_id',
    ];

    public function facultyStaff()
    {
        return $this->belongsTo(FacultyStaff::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewedByCounselor()
    {
        return $this->belongsTo(Counselor::class, 'reviewed_by_counselor_id');
    }
}
