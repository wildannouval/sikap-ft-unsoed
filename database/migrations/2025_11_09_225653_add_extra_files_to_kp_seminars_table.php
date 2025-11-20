<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (!Schema::hasColumn('kp_seminars', 'slides_path')) {
                $table->string('slides_path')->nullable()->after('berkas_laporan_path');
            }
            if (!Schema::hasColumn('kp_seminars', 'bukti_bimbingan_path')) {
                $table->string('bukti_bimbingan_path')->nullable()->after('slides_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            if (Schema::hasColumn('kp_seminars', 'slides_path')) {
                $table->dropColumn('slides_path');
            }
            if (Schema::hasColumn('kp_seminars', 'bukti_bimbingan_path')) {
                $table->dropColumn('bukti_bimbingan_path');
            }
        });
    }
};
