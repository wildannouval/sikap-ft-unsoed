<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Sesuaikan daftar status di bawah ini dengan yang kamu pakai.
        // Tambahkan 'kp_sedang_berjalan' ke dalam daftar enum.
        DB::statement("
            ALTER TABLE kerja_praktiks
            MODIFY COLUMN status ENUM(
                'review_komisi',
                'review_bapendik',
                'spk_terbit',
                'kp_sedang_berjalan',
                'seminar_diajukan',
                'seminar_dijadwalkan',
                'nilai_terbit',
                'ditolak'
            ) NOT NULL DEFAULT 'review_komisi'
        ");
    }

    public function down(): void
    {
        // Rollback dengan MENGHAPUS nilai 'kp_sedang_berjalan'
        // Pastikan tidak ada baris yang masih bernilai 'kp_sedang_berjalan'
        DB::statement("
            UPDATE kerja_praktiks
            SET status = 'spk_terbit'
            WHERE status = 'kp_sedang_berjalan'
        ");

        DB::statement("
            ALTER TABLE kerja_praktiks
            MODIFY COLUMN status ENUM(
                'review_komisi',
                'review_bapendik',
                'spk_terbit',
                'seminar_diajukan',
                'seminar_dijadwalkan',
                'nilai_terbit',
                'ditolak'
            ) NOT NULL DEFAULT 'review_komisi'
        ");
    }
};
