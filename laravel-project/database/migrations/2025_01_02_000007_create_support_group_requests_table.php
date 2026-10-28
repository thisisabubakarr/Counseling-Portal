<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_group_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_name');
            $table->string('roll_number');
            $table->text('reason');
            $table->json('topics')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_group_requests');
    }
};
