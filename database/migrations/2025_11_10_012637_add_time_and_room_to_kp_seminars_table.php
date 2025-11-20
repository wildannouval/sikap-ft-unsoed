<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kp_seminars', 'jam_mulai')) {
                $table->string('jam_mulai', 5)->nullable()->after('tanggal_seminar');   // "HH:MM"
            }
            if (!Schema::hasColumn('kp_seminars', 'jam_selesai')) {
                $table->string('jam_selesai', 5)->nullable()->after('jam_mulai');      // "HH:MM"
            }
        });
    }

    public function down(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (Schema::hasColumn('kp_seminars', 'jam_mulai')) {
                $table->dropColumn('jam_mulai');
            }
            if (Schema::hasColumn('kp_seminars', 'jam_selesai')) {
                $table->dropColumn('jam_selesai');
            }
        });
    }
};
