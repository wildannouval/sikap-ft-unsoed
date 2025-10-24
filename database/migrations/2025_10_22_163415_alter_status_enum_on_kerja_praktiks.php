<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah enum status kerja_praktiks ke skema baru
        DB::statement("
            ALTER TABLE kerja_praktiks
            MODIFY COLUMN status ENUM(
                'review_komisi',
                'review_bapendik',
                'ditolak',
                'spk_terbit'
            ) NOT NULL DEFAULT 'review_komisi'
        ");

        // Normalisasi data lama (opsional, aman diabaikan jika enum lama tidak ada)
        @DB::statement("UPDATE kerja_praktiks SET status = 'review_komisi'   WHERE status IN ('Diajukan','Pending','Menunggu')");
        @DB::statement("UPDATE kerja_praktiks SET status = 'review_bapendik' WHERE status IN ('Disetujui','Approved')");
        @DB::statement("UPDATE kerja_praktiks SET status = 'ditolak'         WHERE status IN ('Ditolak','Rejected')");
    }

    public function down(): void
    {
        // Kembalikan ke enum lama (sesuaikan jika perlu)
        DB::statement("
            ALTER TABLE kerja_praktiks
            MODIFY COLUMN status ENUM('Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Diajukan'
        ");

        @DB::statement("UPDATE kerja_praktiks SET status = 'Diajukan'  WHERE status = 'review_komisi'");
        @DB::statement("UPDATE kerja_praktiks SET status = 'Disetujui' WHERE status IN ('review_bapendik','spk_terbit')");
        @DB::statement("UPDATE kerja_praktiks SET status = 'Ditolak'   WHERE status = 'ditolak'");
    }
};
