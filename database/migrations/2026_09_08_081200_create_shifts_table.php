<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // Pagi, Siang, Malam
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('tolerance_late')->default(15);
            $table->boolean('is_overnight')->default(false);
            $table->string('color', 20)->default('#0d6efd');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
