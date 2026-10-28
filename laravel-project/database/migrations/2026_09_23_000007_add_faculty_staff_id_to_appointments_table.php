<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * An appointment now belongs to either a student OR a faculty/staff member
 * (never both — enforced in the controllers, not the schema, since Oracle
 * doesn't make a clean cross-column constraint worth the complexity here).
 * student_id/roll_number become nullable via raw DDL rather than
 * Blueprint::change(), since this app has no doctrine/dbal dependency and
 * targets Oracle specifically — see CLAUDE.md on not adding dependencies
 * beyond what's needed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('faculty_staff_id')->nullable()->after('student_id')->constrained('faculty_staff')->nullOnDelete();
            $table->string('faculty_staff_name')->nullable()->after('roll_number');
            $table->string('employee_id')->nullable()->after('faculty_staff_name');
        });

        DB::statement('ALTER TABLE appointments MODIFY (student_id NULL)');
        DB::statement('ALTER TABLE appointments MODIFY (roll_number NULL)');
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_staff_id');
            $table->dropColumn(['faculty_staff_name', 'employee_id']);
        });
    }
};
