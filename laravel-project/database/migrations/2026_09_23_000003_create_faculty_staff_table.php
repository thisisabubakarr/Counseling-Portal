<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('cms_employee_id')->nullable()->unique();
            $table->string('password');
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_staff');
    }
};
