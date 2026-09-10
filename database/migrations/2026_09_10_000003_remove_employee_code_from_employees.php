<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('employees', 'employee_code')) {
            Schema::table('employees', function (Blueprint $table) {
                // drop unique index dulu jika ada
                try {
                    $table->dropUnique(['employee_code']);
                } catch (\Throwable $e) {}
                $table->dropColumn('employee_code');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('employees', 'employee_code')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('employee_code', 20)->unique()->nullable()->after('office_location_id');
            });
        }
    }
};
