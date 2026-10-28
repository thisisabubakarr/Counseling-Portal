<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('news_code')->unique();
            $table->string('title');
            $table->text('body');
            $table->date('event_date')->nullable();
            $table->foreignId('counselor_id')->nullable()->constrained('counselors')->nullOnDelete();
            $table->string('counselor_name')->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
