<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->string('time')->nullable();
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
