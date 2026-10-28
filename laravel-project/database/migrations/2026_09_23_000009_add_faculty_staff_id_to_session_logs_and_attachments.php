<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_logs', function (Blueprint $table) {
            $table->foreignId('faculty_staff_id')->nullable()->after('student_id')->constrained('faculty_staff')->nullOnDelete();
        });

        DB::statement('ALTER TABLE session_logs MODIFY (student_id NULL)');

        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('faculty_staff_id')->nullable()->after('student_id')->constrained('faculty_staff')->nullOnDelete();
        });

        DB::statement('ALTER TABLE attachments MODIFY (student_id NULL)');
    }

    public function down(): void
    {
        Schema::table('session_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_staff_id');
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('faculty_staff_id');
        });
    }
};
