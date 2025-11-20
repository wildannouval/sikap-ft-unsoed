<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kp_seminars', 'ba_scan_path')) {
                $table->string('ba_scan_path')->nullable()->after('berkas_laporan_path');
            }
            // pastikan status 'dinilai' tersedia di enum/varchar kolom status
            // jika status bertipe ENUM, tambahkan value baru sesuai DBMS; bila VARCHAR, tidak perlu.
        });
    }

    public function down(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (Schema::hasColumn('kp_seminars', 'ba_scan_path')) {
                $table->dropColumn('ba_scan_path');
            }
        });
    }
};
