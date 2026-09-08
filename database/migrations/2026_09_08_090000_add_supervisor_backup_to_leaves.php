<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('backup_user_id')->nullable()->after('supervisor_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropForeign(['backup_user_id']);
            $table->dropColumn(['supervisor_id','backup_user_id']);
        });
    }
};
