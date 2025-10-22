<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_pengantars', function (Blueprint $table) {
            // Tambah kolom baru
            $table->foreignId('signatory_id')->nullable()->after('ttd_signed_at')->constrained('signatories')->nullOnDelete();
            $table->string('ttd_signed_by_name')->nullable()->after('signatory_id');
            $table->string('ttd_signed_by_position')->nullable()->after('ttd_signed_by_name');
            $table->string('ttd_signed_by_nip')->nullable()->after('ttd_signed_by_position');
        });

        // Jika kolom lama ttd_signed_by ada & tipe-nya kacau (int/string), biarkan dulu; setelah data lama migrate, bisa di-drop.
        // Contoh migrasi ringan (opsional): salin string lama ke ttd_signed_by_name jika tipe-nya string.
        // DB::statement("UPDATE surat_pengantars SET ttd_signed_by_name = ttd_signed_by WHERE ttd_signed_by IS NOT NULL");

        // (Opsional) Drop kolom lama kalau sudah yakin:
        // Schema::table('surat_pengantars', function (Blueprint $table) {
        //     $table->dropColumn('ttd_signed_by');
        // });
    }

    public function down(): void
    {
        Schema::table('surat_pengantars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signatory_id');
            $table->dropColumn(['ttd_signed_by_name','ttd_signed_by_position','ttd_signed_by_nip']);
            // (Opsional) tambah kembali ttd_signed_by jika kamu drop di up()
            // $table->string('ttd_signed_by')->nullable();
        });
    }
};
