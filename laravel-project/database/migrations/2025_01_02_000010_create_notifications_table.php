<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_code')->unique();
            $table->string('to_role');
            $table->foreignId('to_student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('to_counselor_id')->nullable()->constrained('counselors')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->timestamp('read_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['to_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
