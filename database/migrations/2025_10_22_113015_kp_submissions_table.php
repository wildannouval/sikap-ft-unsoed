<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kp_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();

            // Data pengajuan mahasiswa
            $table->string('judul_kp');
            $table->string('instansi');              // / lokasi penelitian
            $table->string('lokasi')->nullable();    // detail alamat jika perlu
            $table->string('file_proposal_path');    // path ke file proposal
            $table->string('file_suket_path');       // path ke file surat keterangan

            // Alur & otorisasi
            $table->enum('status', [
                'Diajukan',            // baru diajukan mahasiswa
                'DitinjauKomisi',      // sedang direview komisi (opsional)
                'DitolakKomisi',       // ditolak komisi
                'DitunjukPembimbing',  // komisi menunjuk dosen pembimbing
                'DiteruskanBapendik',  // (opsional) jika kamu mau step terpisah
                'DiterbitkanSPK',      // bapendik menerbitkan SPK
            ])->default('Diajukan');

            // Komisi & pembimbing
            $table->foreignId('dosen_komisi_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->text('catatan_review')->nullable(); // catatan komisi saat review

            // SPK (diterbitkan Bapendik)
            $table->string('nomor_spk')->nullable();
            $table->date('tanggal_terbit_spk')->nullable();
            $table->foreignId('signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->timestamp('ttd_signed_at')->nullable();
            $table->string('ttd_signed_by_name')->nullable();
            $table->string('ttd_signed_by_position')->nullable();
            $table->string('ttd_signed_by_nip')->nullable();

            // QR verification
            $table->uuid('qr_token')->nullable()->unique();
            $table->timestamp('qr_expires_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'nomor_spk']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('kp_submissions');
    }
};
