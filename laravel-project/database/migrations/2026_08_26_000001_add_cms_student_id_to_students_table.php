<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('cms_student_id')->nullable()->unique()->after('roll_number');
        });

        DB::table('students')->whereNull('cms_student_id')->orderBy('id')->each(function ($student) {
            DB::table('students')->where('id', $student->id)->update([
                'cms_student_id' => $student->roll_number,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('cms_student_id');
        });
    }
};
