<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code', 20)->unique();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('join_date');
            $table->enum('employment_status', ['kontrak','tetap','magang','probation','resigned'])->default('kontrak');
            $table->date('contract_end_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_account', 50)->nullable();
            $table->string('bpjs_kes', 20)->nullable();
            $table->string('bpjs_tk', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
