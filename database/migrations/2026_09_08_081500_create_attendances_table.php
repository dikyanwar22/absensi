<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            // LAT/LNG TRACKING - WAJIB
            $table->decimal('lat_in', 10, 8)->nullable();
            $table->decimal('lng_in', 11, 8)->nullable();
            $table->integer('accuracy_in')->nullable();
            $table->text('address_in')->nullable();
            $table->decimal('lat_out', 10, 8)->nullable();
            $table->decimal('lng_out', 11, 8)->nullable();
            $table->integer('accuracy_out')->nullable();
            $table->text('address_out')->nullable();
            $table->integer('distance_in_meter')->nullable();
            $table->integer('distance_out_meter')->nullable();
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->enum('status', ['hadir','terlambat','pulang_cepat','alpha'])->default('hadir');
            $table->integer('late_minutes')->default(0);
            $table->decimal('overtime_hours', 4, 2)->default(0);
            $table->boolean('is_fake_gps')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
