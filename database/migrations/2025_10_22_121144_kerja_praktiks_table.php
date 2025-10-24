<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kerja_praktiks', function (Blueprint $table) {
            $table->id();

            // siapa pengaju
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();

            // data pengajuan
            $table->string('judul_kp');
            $table->string('lokasi_kp');
            $table->string('proposal_path')->nullable();
            $table->string('surat_keterangan_path')->nullable();

            // status alur
            // Diajukan -> Diproses (komisi) -> Diterbitkan (SPK) / Ditolak
            $table->enum('status', ['Diajukan', 'Diproses', 'Diterbitkan', 'Ditolak'])->default('Diajukan');
            $table->text('catatan')->nullable(); // catatan dari komisi/bap/dll

            // penetapan oleh komisi
            $table->foreignId('komisi_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->constrained('users')->nullOnDelete();

            // penerbitan SPK (oleh Bapendik)
            $table->string('nomor_spk')->nullable();
            $table->foreignId('signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->timestamp('ttd_signed_at')->nullable();

            // verifikasi/QR SPK
            $table->uuid('qr_token')->nullable()->unique();
            $table->timestamp('qr_expires_at')->nullable();

            $table->timestamps();

            // index tambahan biar query enak
            $table->index(['status', 'created_at']);

            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('dosens')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kerja_praktiks');
    }
};
