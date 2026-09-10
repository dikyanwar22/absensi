<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('PT. AbsensiKu');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable(); // storage/app/public/company/logo.png
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // seed default
        DB::table('company_settings')->insert([
            'name' => 'PT. AbsensiKu',
            'address' => 'Jl. Contoh No.1, Jakarta - Indonesia',
            'phone' => '021-12345678',
            'email' => 'hrd@absensiku.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
