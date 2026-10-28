<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_group_requests', function (Blueprint $table) {
            $table->foreignId('faculty_staff_id')->nullable()->after('student_id')->constrained('faculty_staff')->nullOnDelete();
            $table->string('faculty_staff_name')->nullable()->after('roll_number');
            $table->string('employee_id')->nullable()->after('faculty_staff_name');
        });

        DB::statement('ALTER TABLE support_group_requests MODIFY (student_id NULL)');
        DB::statement('ALTER TABLE support_group_requests MODIFY (roll_number NULL)');
    }

    public function down(): void
    {
        Schema::table('support_group_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_staff_id');
            $table->dropColumn(['faculty_staff_name', 'employee_id']);
        });
    }
};
