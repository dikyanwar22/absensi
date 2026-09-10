<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // Potongan Serikat, Potongan Liburan, Ganti Rugi
            $table->decimal('default_amount', 12, 2)->default(0); // HRD isi default Rp
            $table->boolean('is_active')->default(true); // soft delete via is_active=false + softDeletes
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
            $table->index('is_active');
        });

        Schema::create('payroll_deduction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_detail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deduction_type_id')->nullable()->constrained('deduction_types')->nullOnDelete();
            $table->string('name', 100); // snapshot nama saat generate, untuk slip
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            $table->index('payroll_detail_id');
        });

        // Seed contoh potongan awal (opsional)
        // DB::table('deduction_types')->insert([...]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_deduction_items');
        Schema::dropIfExists('deduction_types');
    }
};
