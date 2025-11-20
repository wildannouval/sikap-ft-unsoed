<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kerja_praktiks', function (Blueprint $table) {
            $table->id();

            // FK ke mahasiswas (PK kustom: mahasiswa_id)
            $table->unsignedBigInteger('mahasiswa_id');

            // Data pengajuan
            $table->string('judul_kp');
            $table->string('lokasi_kp');
            $table->string('proposal_path')->nullable();
            $table->string('surat_keterangan_path')->nullable();

            // Status alur (selaras dengan App\Models\KerjaPraktik::statuses())
            $table->enum('status', [
                'review_komisi',
                'review_bapendik',
                'ditolak',
                'spk_terbit',
                'kp_sedang_berjalan',
                'seminar_diajukan',
                'seminar_dijadwalkan',
                'nilai_terbit',
            ])->default('review_komisi');

            $table->text('catatan')->nullable(); // catatan peninjauan, alasan, dsb.

            // Penetapan dosen pembimbing (FK ke dosens dengan PK kustom: dosen_id)
            $table->unsignedBigInteger('dosen_pembimbing_id')->nullable();

            // Penerbitan SPK (oleh Bapendik)
            $table->string('nomor_spk')->nullable();
            $table->date('tanggal_terbit_spk')->nullable();
            $table->foreignId('signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->timestamp('ttd_signed_at')->nullable();

            // Verifikasi/QR SPK
            $table->uuid('spk_qr_token')->nullable()->unique();
            $table->uuid('qr_token')->nullable()->unique(); // jika masih dipakai di tempat lain
            $table->timestamp('qr_expires_at')->nullable();

            $table->timestamps();

            // Index untuk query umum
            $table->index(['status', 'created_at']);

            // --- Definisi FK manual untuk PK kustom ---
            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')->on('mahasiswas')
                ->cascadeOnDelete();

            $table->foreign('dosen_pembimbing_id')
                ->references('dosen_id')->on('dosens')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerja_praktiks');
    }
};
