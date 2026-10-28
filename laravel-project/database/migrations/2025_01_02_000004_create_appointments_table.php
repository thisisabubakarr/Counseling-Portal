<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('student_name');
            $table->string('roll_number');
            $table->foreignId('counselor_id')->constrained('counselors');
            $table->string('counselor_name');
            $table->date('date');
            $table->string('slot');
            $table->string('status')->default('pending');
            $table->boolean('suicidal_thoughts')->default(false);
            $table->text('mental_health_questions')->nullable();
            $table->json('presenting_concerns')->nullable();
            $table->json('support_group_topics')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->text('notes')->nullable();
            $table->text('counsellor_notes')->nullable();
            $table->timestamps();

            $table->index(['counselor_id', 'date']);
            $table->index(['status']);
        });

        Schema::create('appointment_reschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->date('from_date');
            $table->string('from_slot');
            $table->date('to_date');
            $table->string('to_slot');
            $table->string('by');
            $table->timestamp('at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reschedules');
        Schema::dropIfExists('appointments');
    }
};
