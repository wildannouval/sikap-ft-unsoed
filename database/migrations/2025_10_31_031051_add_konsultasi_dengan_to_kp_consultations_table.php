<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kp_consultations', function (Blueprint $table) {
            // tambah kolom nullable, letakkan setelah FK dosen_pembimbing_id biar rapi
            if (!Schema::hasColumn('kp_consultations', 'konsultasi_dengan')) {
                $table->string('konsultasi_dengan')->nullable()->after('dosen_pembimbing_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kp_consultations', function (Blueprint $table) {
            if (Schema::hasColumn('kp_consultations', 'konsultasi_dengan')) {
                $table->dropColumn('konsultasi_dengan');
            }
        });
    }
};
