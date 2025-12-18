<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan file laporan final mahasiswa
            $table->string('laporan_final_path')->nullable()->after('distribusi_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('kp_seminars', function (Blueprint $table) {
            $table->dropColumn('laporan_final_path');
        });
    }
};
