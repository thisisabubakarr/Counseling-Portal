<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reschedule is no longer an immediate change — it's a proposal that the
 * other party (counselor, or the student/faculty-staff owner) must
 * approve. The original date/slot stay put on the appointment row until
 * approved; the PROPOSED date/slot live here so SlotService can hold that
 * slot for this appointment without releasing the original one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('pending_reschedule_date')->nullable()->after('slot');
            $table->string('pending_reschedule_slot')->nullable()->after('pending_reschedule_date');
            $table->string('reschedule_requested_by')->nullable()->after('pending_reschedule_slot');
            $table->string('status_before_reschedule')->nullable()->after('reschedule_requested_by');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['pending_reschedule_date', 'pending_reschedule_slot', 'reschedule_requested_by', 'status_before_reschedule']);
        });
    }
};
