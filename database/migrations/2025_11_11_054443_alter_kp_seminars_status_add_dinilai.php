<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sesuaikan daftar ENUM dengan seluruh status yang dipakai di app
        // pastikan urutan dan nama persis sama dengan konstanta di Model
        DB::statement("
            ALTER TABLE `kp_seminars`
            MODIFY `status` ENUM(
                'diajukan',
                'disetujui_pembimbing',
                'dijadwalkan',
                'ba_terbit',
                'dinilai',
                'ditolak'
            ) NOT NULL DEFAULT 'diajukan'
        ");
    }

    public function down(): void
    {
        // rollback: hapus 'dinilai' (akan gagal jika ada baris berstatus 'dinilai')
        // opsi aman: ubah dulu semua 'dinilai' jadi 'ba_terbit', baru turunkan enum
        DB::statement("UPDATE `kp_seminars` SET `status` = 'ba_terbit' WHERE `status` = 'dinilai'");

        DB::statement("
            ALTER TABLE `kp_seminars`
            MODIFY `status` ENUM(
                'diajukan',
                'disetujui_pembimbing',
                'dijadwalkan',
                'ba_terbit',
                'ditolak'
            ) NOT NULL DEFAULT 'diajukan'
        ");
    }
};
