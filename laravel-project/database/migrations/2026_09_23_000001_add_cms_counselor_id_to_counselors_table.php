<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('counselors', function (Blueprint $table) {
            $table->string('cms_counselor_id')->nullable()->unique()->after('counselor_code');
        });

        DB::table('counselors')->whereNull('cms_counselor_id')->orderBy('id')->each(function ($counselor) {
            DB::table('counselors')->where('id', $counselor->id)->update([
                'cms_counselor_id' => $counselor->counselor_code,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('counselors', function (Blueprint $table) {
            $table->dropColumn('cms_counselor_id');
        });
    }
};
