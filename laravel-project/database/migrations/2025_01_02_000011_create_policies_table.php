<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_code')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('description')->nullable();
            $table->longText('body');
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->string('counselor_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
