<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM untuk menghapus 'dijadwalkan' dan memastikan urutannya benar
        // Pastikan tidak ada data yang memiliki status 'dijadwalkan' sebelum menjalankan ini,
        // atau update dulu datanya menjadi 'disetujui_pembimbing' atau 'ba_terbit'.

        // Update data lama (safety net)
        DB::table('kp_seminars')
            ->where('status', 'dijadwalkan')
            ->update(['status' => 'disetujui_pembimbing']);

        DB::statement("ALTER TABLE kp_seminars MODIFY COLUMN status ENUM('diajukan','disetujui_pembimbing','selesai','revisi','gagal','ba_terbit','dinilai','ditolak') NOT NULL DEFAULT 'diajukan'");
    }

    public function down(): void
    {
        // Kembalikan jika rollback
        DB::statement("ALTER TABLE kp_seminars MODIFY COLUMN status ENUM('diajukan','disetujui_pembimbing','dijadwalkan','selesai','revisi','gagal','ba_terbit','dinilai','ditolak') NOT NULL DEFAULT 'diajukan'");
    }
};
