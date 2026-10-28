<?php

use App\Http\Controllers\Auth\CmsSsoController;
use App\Http\Controllers\Auth\CounselorAuthController;
use App\Http\Controllers\Auth\FacultyStaffAuthController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Counselor\AppointmentController as CounselorAppointmentController;
use App\Http\Controllers\Counselor\DashboardController as CounselorDashboardController;
use App\Http\Controllers\Counselor\FacultyStaffController as CounselorFacultyStaffController;
use App\Http\Controllers\Counselor\NewsController;
use App\Http\Controllers\Counselor\NotificationController as CounselorNotificationController;
use App\Http\Controllers\Counselor\PolicyController as CounselorPolicyController;
use App\Http\Controllers\Counselor\SlotController;
use App\Http\Controllers\Counselor\StudentController;
use App\Http\Controllers\Counselor\StudentReferralController as CounselorStudentReferralController;
use App\Http\Controllers\Counselor\SupportGroupController as CounselorSupportGroupController;
use App\Http\Controllers\Counselor\TaskController;
use App\Http\Controllers\Dev\SsoSimulatorController;
use App\Http\Controllers\FacultyStaff\AppointmentController as FacultyAppointmentController;
use App\Http\Controllers\FacultyStaff\DashboardController as FacultyDashboardController;
use App\Http\Controllers\FacultyStaff\NotificationController as FacultyNotificationController;
use App\Http\Controllers\FacultyStaff\ProfileController as FacultyProfileController;
use App\Http\Controllers\FacultyStaff\StudentReferralController as FacultyStudentReferralController;
use App\Http\Controllers\FacultyStaff\SupportGroupController as FacultySupportGroupController;
use App\Http\Controllers\Student\AppointmentController as StudentAppointmentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\SupportGroupController as StudentSupportGroupController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Entry point from the CMS — not nested under student/, since a student or
// counselor arrives here unauthenticated, mid-handoff, not as a logged-in
// user. This is the *only* way into the app now — see CLAUDE.md and
// bnu-counselor-cms-integration-architecture.md: the CMS owns authentication
// entirely, for both roles.
Route::post('/sso/callback', [CmsSsoController::class, 'callback'])->name('sso.callback');

// Session teardown only — there is no login/register here anymore, the CMS
// owns that. Kept under the student/counselor prefixes since these are
// authenticated actions taken from within the respective portal.
Route::prefix('student')->name('student.')->group(function () {
    Route::post('logout', [StudentAuthController::class, 'logout'])->name('logout');
});

Route::prefix('counselor')->name('counselor.')->group(function () {
    Route::post('logout', [CounselorAuthController::class, 'logout'])->name('logout');
});

Route::prefix('faculty')->name('faculty.')->group(function () {
    Route::post('logout', [FacultyStaffAuthController::class, 'logout'])->name('logout');
});

// Stands in for the real BNU CMS until it exists — see
// App\Http\Controllers\Dev\SsoSimulatorController. Never registered outside
// local development.
if (app()->environment('local')) {
    Route::prefix('dev/sso')->name('dev.sso.')->group(function () {
        Route::get('/', [SsoSimulatorController::class, 'index'])->name('index');
        Route::post('/', [SsoSimulatorController::class, 'mint'])->name('mint');
    });
}

// Student portal
Route::prefix('student')->name('student.')->middleware('student.auth')->group(function () {
    Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('appointments/create', [StudentAppointmentController::class, 'create'])->name('appointments.create');
    Route::get('appointments/available-slots', [StudentAppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
    Route::post('appointments', [StudentAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{appointment}', [StudentAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{appointment}/cancel', [StudentAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/reschedule', [StudentAppointmentController::class, 'requestReschedule'])->name('appointments.reschedule');
    Route::post('appointments/{appointment}/reschedule/respond', [StudentAppointmentController::class, 'respondToReschedule'])->name('appointments.reschedule.respond');

    Route::get('support-group', [StudentSupportGroupController::class, 'index'])->name('support-group.index');
    Route::get('support-group/create', [StudentSupportGroupController::class, 'create'])->name('support-group.create');
    Route::post('support-group', [StudentSupportGroupController::class, 'store'])->name('support-group.store');

    Route::get('notifications', [StudentNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [StudentNotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('policy', [ProfileController::class, 'policy'])->name('policy');
    Route::get('policy/{document}', [ProfileController::class, 'policyShow'])->name('policy.show');
    Route::post('next-session/respond', [ProfileController::class, 'respondNextSession'])->name('next-session.respond');
    Route::post('attachments', [ProfileController::class, 'storeAttachment'])->name('attachments.store');
    Route::get('meetings', [ProfileController::class, 'meetings'])->name('meetings');
});

// Faculty & Staff portal — deliberately mirrors the student portal above
// (same Appointment/SupportGroupRequest/Notification/Policy models, scoped
// by faculty_staff_id instead of student_id) so faculty/staff get the exact
// same counseling features, plus the one addition: referring a student.
Route::prefix('faculty')->name('faculty.')->middleware('faculty.auth')->group(function () {
    Route::get('dashboard', [FacultyDashboardController::class, 'index'])->name('dashboard');

    Route::get('appointments/create', [FacultyAppointmentController::class, 'create'])->name('appointments.create');
    Route::get('appointments/available-slots', [FacultyAppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
    Route::post('appointments', [FacultyAppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{appointment}', [FacultyAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{appointment}/cancel', [FacultyAppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/reschedule', [FacultyAppointmentController::class, 'requestReschedule'])->name('appointments.reschedule');
    Route::post('appointments/{appointment}/reschedule/respond', [FacultyAppointmentController::class, 'respondToReschedule'])->name('appointments.reschedule.respond');

    Route::get('support-group', [FacultySupportGroupController::class, 'index'])->name('support-group.index');
    Route::get('support-group/create', [FacultySupportGroupController::class, 'create'])->name('support-group.create');
    Route::post('support-group', [FacultySupportGroupController::class, 'store'])->name('support-group.store');

    Route::get('notifications', [FacultyNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [FacultyNotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('policy', [FacultyProfileController::class, 'policy'])->name('policy');
    Route::get('policy/{document}', [FacultyProfileController::class, 'policyShow'])->name('policy.show');
    Route::post('attachments', [FacultyProfileController::class, 'storeAttachment'])->name('attachments.store');
    Route::get('meetings', [FacultyProfileController::class, 'meetings'])->name('meetings');

    Route::get('referrals', [FacultyStudentReferralController::class, 'index'])->name('referrals.index');
    Route::post('referrals', [FacultyStudentReferralController::class, 'store'])->name('referrals.store');
});

// Counselor portal
Route::prefix('counselor')->name('counselor.')->middleware('counselor.auth')->group(function () {
    Route::get('dashboard', [CounselorDashboardController::class, 'index'])->name('dashboard');

    Route::get('appointments', [CounselorAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/available-slots', [CounselorAppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
    Route::get('appointments/{appointment}', [CounselorAppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{appointment}/accept', [CounselorAppointmentController::class, 'accept'])->name('appointments.accept');
    Route::post('appointments/{appointment}/reject', [CounselorAppointmentController::class, 'reject'])->name('appointments.reject');
    Route::post('appointments/{appointment}/reschedule', [CounselorAppointmentController::class, 'requestReschedule'])->name('appointments.reschedule');
    Route::post('appointments/{appointment}/reschedule/respond', [CounselorAppointmentController::class, 'respondToReschedule'])->name('appointments.reschedule.respond');
    Route::post('appointments/{appointment}/notes', [CounselorAppointmentController::class, 'updateNotes'])->name('appointments.notes');

    Route::get('support-group-requests', [CounselorSupportGroupController::class, 'index'])->name('support-group.index');
    Route::post('support-group-requests/{supportGroupRequest}', [CounselorSupportGroupController::class, 'update'])->name('support-group.update');

    Route::get('student-referrals', [CounselorStudentReferralController::class, 'index'])->name('student-referrals.index');
    Route::post('student-referrals/{studentReferral}', [CounselorStudentReferralController::class, 'update'])->name('student-referrals.update');

    Route::get('students', [StudentController::class, 'index'])->name('students.index');
    Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');
    Route::post('students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::post('students/{student}/next-session/suggest', [StudentController::class, 'suggestNextSession'])->name('students.next-session.suggest');

    Route::get('faculty-staff', [CounselorFacultyStaffController::class, 'index'])->name('faculty-staff.index');
    Route::get('faculty-staff/{facultyStaff}', [CounselorFacultyStaffController::class, 'show'])->name('faculty-staff.show');

    Route::get('slots', [SlotController::class, 'index'])->name('slots.index');
    Route::post('slots', [SlotController::class, 'store'])->name('slots.store');
    Route::delete('slots/{blockedSlot}', [SlotController::class, 'destroy'])->name('slots.destroy');

    Route::get('news', [NewsController::class, 'index'])->name('news.index');
    Route::post('news', [NewsController::class, 'store'])->name('news.store');
    Route::delete('news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');

    Route::get('policies', [CounselorPolicyController::class, 'index'])->name('policy.index');
    Route::post('policies', [CounselorPolicyController::class, 'store'])->name('policy.store');
    Route::put('policies/{policy}', [CounselorPolicyController::class, 'update'])->name('policy.update');
    Route::delete('policies/{policy}', [CounselorPolicyController::class, 'destroy'])->name('policy.destroy');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('notifications', [CounselorNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [CounselorNotificationController::class, 'markRead'])->name('notifications.read');
});
