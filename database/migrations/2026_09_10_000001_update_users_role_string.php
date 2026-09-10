<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah users.role dari enum('hrd','supervisor','staff') menjadi VARCHAR(50) agar bisa role dinamis dari jabatan (mis manager_finance)
        try {
            DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'staff'");
        } catch (\Throwable $e) {
            // fallback: coba via Schema jika DBAL ada
            try {
                Schema::table('users', function ($table) {
                    $table->string('role', 50)->default('staff')->change();
                });
            } catch (\Throwable $e2) {}
        }

        // menu_settings.role sudah VARCHAR, pastikan cukup panjang (50)
        try {
            DB::statement("ALTER TABLE `menu_settings` MODIFY `role` VARCHAR(50) NOT NULL");
        } catch (\Throwable $e) {}

        // Update existing staff/supervisor/hrd tetap, tidak perlu backfill
        // Untuk posisi yang sudah ada, tidak perlu ubah role, biarkan manual via edit jabatan nanti akan jadi manager_finance dll
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('hrd','supervisor','staff') NOT NULL DEFAULT 'staff'");
        } catch (\Throwable $e) {}
    }
};
