<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7)->unique(); // 2026-09
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft','locked','paid'])->default('draft');
            $table->integer('total_employees')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('locked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('thr', 12, 2)->default(0);
            $table->json('deductions')->nullable();
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('total_deduction', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->json('attendance_summary')->nullable();
            $table->text('notes')->nullable();
            $table->string('slip_pdf_path')->nullable();
            $table->timestamps();
            $table->unique(['payroll_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
        Schema::dropIfExists('payrolls');
    }
};
