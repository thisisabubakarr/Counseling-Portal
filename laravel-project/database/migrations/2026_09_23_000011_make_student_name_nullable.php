<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Follow-up fix: the earlier migrations that added faculty_staff_id to
 * appointments/support_group_requests made student_id and roll_number
 * nullable but missed student_name, which is still NOT NULL — breaking
 * every faculty/staff booking with ORA-01400.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE appointments MODIFY (student_name NULL)');
        DB::statement('ALTER TABLE support_group_requests MODIFY (student_name NULL)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE appointments MODIFY (student_name NOT NULL)');
        DB::statement('ALTER TABLE support_group_requests MODIFY (student_name NOT NULL)');
    }
};
