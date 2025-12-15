<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `kp_seminars`
            MODIFY `status` ENUM(
                'diajukan',
                'disetujui_pembimbing',
                'dijadwalkan',
                'selesai',
                'revisi',
                'gagal',
                'ba_terbit',
                'dinilai',
                'ditolak'
            ) NOT NULL DEFAULT 'diajukan'
        ");
    }

    public function down(): void
    {
        // Amankan rollback kalau ada data yang pakai status baru
        DB::statement("
            UPDATE `kp_seminars`
            SET `status` = 'ba_terbit'
            WHERE `status` IN ('selesai','revisi','gagal')
        ");

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
};
