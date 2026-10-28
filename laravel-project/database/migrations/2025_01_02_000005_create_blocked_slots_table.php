<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            $table->string('blocked_code')->unique();
            $table->foreignId('counselor_id')->constrained('counselors')->cascadeOnDelete();
            $table->date('date');
            $table->string('slot');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['counselor_id', 'date', 'slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_slots');
    }
};
