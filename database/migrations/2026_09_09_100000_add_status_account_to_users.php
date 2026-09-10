<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('status_account')->default(0)->after('role')->comment('0=pending menunggu ACC HRD, 1=aktif bisa login');
        });

        // backfill: user existing (seed/admin) jadi aktif agar tetap bisa login
        try {
            DB::table('users')->whereNull('status_account')->orWhere('status_account', 0)->update(['status_account' => 1]);
        } catch (\Throwable $e) {
            // fallback via raw
            try { DB::statement("UPDATE users SET status_account = 1 WHERE status_account = 0 OR status_account IS NULL"); } catch (\Throwable $e2) {}
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status_account');
        });
    }
};
