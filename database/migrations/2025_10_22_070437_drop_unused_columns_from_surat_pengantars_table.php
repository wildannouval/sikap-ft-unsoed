<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_pengantars', function (Blueprint $table) {
            // Jika sebelumnya ada index pada uuid, drop index dulu
            // $table->dropIndex(['uuid']); // uncomment kalau pernah buat index

            $table->dropColumn([
                'uuid',
                'tanggal_terbit_surat_pengantar',
                'tanggal_pengambilan_surat_pengantar',
                'ttd_signed_by',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('surat_pengantars', function (Blueprint $table) {
            // Tambahkan kembali kolom jika rollback
            $table->uuid('uuid')->nullable()->after('id');
            $table->date('tanggal_terbit_surat_pengantar')->nullable()->after('tanggal_disetujui_surat_pengantar');
            $table->date('tanggal_pengambilan_surat_pengantar')->nullable()->after('tanggal_terbit_surat_pengantar');
            $table->string('ttd_signed_by')->nullable()->after('ttd_signed_at');

            // Jika dulu pernah pakai index di uuid
            // $table->index('uuid');
        });
    }
};
