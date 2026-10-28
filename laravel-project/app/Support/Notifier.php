<?php

namespace App\Support;

use App\Models\Counselor;
use App\Models\FacultyStaff;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    public static function toStudent(Student $student, string $title, string $message, string $type = 'info', array $meta = []): Notification
    {
        return Notification::create([
            'notification_code' => CodeGenerator::make('NTF'),
            'to_role' => 'student',
            'to_student_id' => $student->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    public static function toCounselor(?Counselor $counselor, string $title, string $message, string $type = 'info', array $meta = []): Notification
    {
        return Notification::create([
            'notification_code' => CodeGenerator::make('NTF'),
            'to_role' => 'counsellor',
            'to_counselor_id' => $counselor?->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    public static function toFacultyStaff(FacultyStaff $facultyStaff, string $title, string $message, string $type = 'info', array $meta = []): Notification
    {
        return Notification::create([
            'notification_code' => CodeGenerator::make('NTF'),
            'to_role' => 'faculty_staff',
            'to_faculty_staff_id' => $facultyStaff->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    public static function toStudentBroadcast(string $title, string $message, string $type = 'info', array $meta = []): Notification
    {
        return Notification::create([
            'notification_code' => CodeGenerator::make('NTF'),
            'to_role' => 'student',
            'to_student_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    public static function mailStudent(Mailable $mailable): void
    {
        $to = config('mail.demo_student_to');
        self::sendBestEffort($to, $mailable);
    }

    public static function mailCounselor(?Counselor $counselor, Mailable $mailable): void
    {
        $to = $counselor?->email ?: config('mail.demo_counselor_to');
        self::sendBestEffort($to, $mailable);
    }

    public static function mailFacultyStaff(?FacultyStaff $facultyStaff, Mailable $mailable): void
    {
        $to = $facultyStaff?->email ?: config('mail.demo_faculty_to');
        self::sendBestEffort($to, $mailable);
    }

    private static function sendBestEffort(?string $to, Mailable $mailable): void
    {
        if (! $to) {
            Log::info('[EMAIL][skipped] missing recipient');

            return;
        }

        try {
            Mail::to($to)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('[EMAIL][failed] '.$e->getMessage());
        }
    }
}
