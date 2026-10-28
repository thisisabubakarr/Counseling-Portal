<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A faculty/staff member referring a STUDENT to counseling — separate from
 * `counseling_requests` (which is a faculty/staff member requesting
 * counseling for themselves) so counselors can manage each type
 * independently, per CLAUDE.md-style separation-of-concerns already used
 * for appointments vs support group requests.
 *
 * `student_id` is nullable: the referring faculty/staff member may not know
 * whether the student already exists in this system, so the referral form
 * accepts a roll number/name even when no matching Student row is found —
 * see StudentReferralController@store.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_code')->unique();
            $table->foreignId('faculty_staff_id')->constrained('faculty_staff')->cascadeOnDelete();
            $table->string('referred_by_name');
            $table->string('referred_by_designation')->nullable();
            $table->string('referred_by_department')->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('student_name');
            $table->string('student_roll_number');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->text('counselor_notes')->nullable();
            $table->foreignId('reviewed_by_counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_referrals');
    }
};
