<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A faculty/staff member requesting counseling for themselves — kept as its
 * own table (not reusing `appointments`) since it has no scheduling/slot
 * concept, just a lightweight request + status flow, same shape as
 * `support_group_requests`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('faculty_staff_id')->constrained('faculty_staff')->cascadeOnDelete();
            $table->string('faculty_staff_name');
            $table->string('employee_id');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('service');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_requests');
    }
};
