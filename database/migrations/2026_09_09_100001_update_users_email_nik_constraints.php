<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop unique constraint on email (allow duplicate email)
        // Index name default: users_email_unique
        try {
            DB::statement('ALTER TABLE `users` DROP INDEX `users_email_unique`');
        } catch (\Throwable $e) {
            // fallback via Laravel Schema
            try {
                Schema::table('users', function ($table) {
                    $table->dropUnique(['email']);
                });
            } catch (\Throwable $e2) {
                // ignore if already dropped or doesn't exist
            }
        }

        // 2. Ensure nik is VARCHAR(9) and unique (angka semua, maks 9)
        // Use raw ALTER to avoid doctrine/dbal requirement
        try {
            // First ensure existing nik values are compatible (pad/truncate not needed, keep as is)
            // Change column to VARCHAR(9) - will truncate if >9 but existing are 6 chars so safe
            // Keep UNIQUE constraint
            DB::statement('ALTER TABLE `users` MODIFY `nik` VARCHAR(9) NULL');
        } catch (\Throwable $e) {
            // If already 9 or error, try without NULL change
            try { DB::statement('ALTER TABLE `users` MODIFY `nik` VARCHAR(9)'); } catch (\Throwable $e2) {}
        }

        // Re-ensure unique index on nik exists (name: users_nik_unique)
        try {
            $hasIndex = false;
            $indexes = DB::select("SHOW INDEX FROM `users` WHERE Key_name = 'users_nik_unique'");
            $hasIndex = count($indexes) > 0;
            if (!$hasIndex) {
                DB::statement('ALTER TABLE `users` ADD UNIQUE `users_nik_unique` (`nik`)');
            }
        } catch (\Throwable $e) {}

        // 3. Backfill existing non-numeric nik to numeric 9-digit if needed? Keep as is for staff demo, but ensure future numeric
        // No auto backfill to avoid breaking demo login via HRD001. Biarkan HRD001 tetap, tapi constraint VARCHAR(9) masih muat (6 char).
        // For any future check, validation will enforce numeric 9.
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `users` MODIFY `nik` VARCHAR(20) NULL');
        } catch (\Throwable $e) {}
        try {
            DB::statement('ALTER TABLE `users` ADD UNIQUE `users_email_unique` (`email`)');
        } catch (\Throwable $e) {
            try {
                Schema::table('users', function ($table) {
                    $table->unique('email');
                });
            } catch (\Throwable $e2) {}
        }
    }
};
