<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('document_path')->nullable();
            $table->enum('status_supervisor', ['pending','approved','rejected'])->default('pending');
            $table->enum('status_hrd', ['pending','approved','rejected'])->default('pending');
            $table->enum('final_status', ['pending','approved','rejected'])->default('pending');
            $table->text('supervisor_note')->nullable();
            $table->text('hrd_note')->nullable();
            $table->foreignId('approved_by_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_hrd_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
