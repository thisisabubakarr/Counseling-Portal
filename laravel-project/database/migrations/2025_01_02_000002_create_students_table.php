<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('roll_number')->unique();
            $table->string('password');
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('semester')->nullable();
            $table->string('session_status')->default('Ongoing');
            $table->boolean('suicide_intent')->default(false);
            $table->boolean('suicide_alert')->default(false);
            $table->date('next_session_date')->nullable();
            $table->date('suggested_next_session_date')->nullable();
            $table->string('next_session_date_status')->default('none');
            $table->foreignId('suggested_by_counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->string('nature_of_consent')->default('Standard');
            $table->string('last_attended_session')->nullable();
            $table->boolean('agreement_accepted')->default(false);
            $table->decimal('cgpa', 3, 2)->nullable();
            $table->json('presenting_concerns')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
