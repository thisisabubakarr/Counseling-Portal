<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use Carbon\Carbon;

class SlotService
{
    // Includes the two reschedule-pending statuses: while a reschedule is
    // awaiting approval, the appointment's ORIGINAL date/slot stays booked
    // (untouched) — only the proposed new slot is held separately, via
    // pending_reschedule_date/slot. See getAvailableSlots()/isSlotTaken().
    public const BOOKED_STATUSES = ['pending', 'accepted', 'rescheduled', 'reschedule_pending_counselor', 'reschedule_pending_user'];

    public const RESCHEDULE_PENDING_STATUSES = ['reschedule_pending_counselor', 'reschedule_pending_user'];

    public static function isWeekday(string $dateStr): bool
    {
        $date = self::parseDate($dateStr);

        return $date && $date->isWeekday();
    }

    public static function isWithin14Days(string $dateStr): bool
    {
        $date = self::parseDate($dateStr);
        if (! $date) {
            return false;
        }

        $today = Carbon::today();
        $max = $today->copy()->addDays(14);

        return $date->betweenIncluded($today, $max);
    }

    public static function generateTimeSlots(): array
    {
        $slots = [];
        for ($hour = 9; $hour < 17; $hour++) {
            $start = sprintf('%02d:00', $hour);
            $end = sprintf('%02d:00', $hour + 1);
            $slots[] = ['start' => $start, 'end' => $end, 'label' => "{$start} - {$end}"];
        }

        return $slots;
    }

    public static function getAvailableSlots(string $dateStr, ?int $counselorId = null, ?int $excludeAppointmentId = null): array
    {
        if (! self::isWeekday($dateStr)) {
            return [];
        }

        $bookedQuery = Appointment::query()
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('date', $dateStr);
        if ($counselorId) {
            $bookedQuery->where('counselor_id', $counselorId);
        }
        if ($excludeAppointmentId) {
            $bookedQuery->where('id', '!=', $excludeAppointmentId);
        }
        $bookedSlots = $bookedQuery->pluck('slot')->all();

        // A pending reschedule proposal holds its PROPOSED slot too, even
        // though the appointment's own `date`/`slot` still point at the
        // original booking (counted above).
        $heldQuery = Appointment::query()
            ->whereIn('status', self::RESCHEDULE_PENDING_STATUSES)
            ->whereDate('pending_reschedule_date', $dateStr);
        if ($counselorId) {
            $heldQuery->where('counselor_id', $counselorId);
        }
        if ($excludeAppointmentId) {
            $heldQuery->where('id', '!=', $excludeAppointmentId);
        }
        $heldSlots = $heldQuery->pluck('pending_reschedule_slot')->all();

        $takenSlots = array_merge($bookedSlots, $heldSlots);

        $blockedQuery = BlockedSlot::query()->whereDate('date', $dateStr);
        if ($counselorId) {
            $blockedQuery->where('counselor_id', $counselorId);
        }
        $blockedSlots = $blockedQuery->pluck('slot')->all();

        $isToday = $dateStr === Carbon::today()->toDateString();
        $nowHHMM = Carbon::now()->format('H:i');

        return array_values(array_filter(self::generateTimeSlots(), function ($slot) use ($takenSlots, $blockedSlots, $isToday, $nowHHMM) {
            if (in_array($slot['label'], $takenSlots, true) || in_array($slot['label'], $blockedSlots, true)) {
                return false;
            }
            if ($isToday && $slot['start'] <= $nowHHMM) {
                return false;
            }

            return true;
        }));
    }

    public static function isSlotTaken(int $counselorId, string $dateStr, string $slot, ?int $excludeAppointmentId = null): bool
    {
        $bookedQuery = Appointment::query()
            ->where('counselor_id', $counselorId)
            ->whereDate('date', $dateStr)
            ->where('slot', $slot)
            ->whereIn('status', self::BOOKED_STATUSES);

        if ($excludeAppointmentId) {
            $bookedQuery->where('id', '!=', $excludeAppointmentId);
        }

        if ($bookedQuery->exists()) {
            return true;
        }

        $heldQuery = Appointment::query()
            ->where('counselor_id', $counselorId)
            ->whereDate('pending_reschedule_date', $dateStr)
            ->where('pending_reschedule_slot', $slot)
            ->whereIn('status', self::RESCHEDULE_PENDING_STATUSES);

        if ($excludeAppointmentId) {
            $heldQuery->where('id', '!=', $excludeAppointmentId);
        }

        return $heldQuery->exists();
    }

    private static function parseDate(string $dateStr): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $dateStr)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
