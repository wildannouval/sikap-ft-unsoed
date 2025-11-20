<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kp_submissions', function (Blueprint $table) {
            $table->id();

            // FK ke mahasiswas(mahasiswa_id)
            $table->unsignedBigInteger('mahasiswa_id');

            $table->string('judul_kp', 255);
            $table->string('instansi', 255);
            $table->string('lokasi', 255)->nullable();
            $table->string('file_proposal_path', 255);
            $table->string('file_suket_path', 255);

            $table->enum('status', [
                'Diajukan',
                'DitinjauKomisi',
                'DitolakKomisi',
                'DitunjukPembimbing',
                'DiteruskanBapendik',
                'DiterbitkanSPK',
            ])->default('Diajukan');

            // FK ke dosens(dosen_id)
            $table->unsignedBigInteger('dosen_komisi_id')->nullable();
            $table->unsignedBigInteger('dosen_pembimbing_id')->nullable();

            $table->text('catatan_review')->nullable();

            $table->string('nomor_spk', 100)->nullable();
            $table->date('tanggal_terbit_spk')->nullable();

            $table->foreignId('signatory_id')
                ->nullable()
                ->constrained('signatories')
                ->nullOnDelete();

            $table->timestamp('ttd_signed_at')->nullable();
            $table->string('ttd_signed_by_name', 150)->nullable();
            $table->string('ttd_signed_by_position', 150)->nullable();
            $table->string('ttd_signed_by_nip', 50)->nullable();

            $table->uuid('qr_token')->nullable()->unique();
            $table->timestamp('qr_expires_at')->nullable();

            $table->timestamps();

            // Definisi FK manual karena PK kustom
            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')->on('mahasiswas')
                ->cascadeOnDelete();

            $table->foreign('dosen_komisi_id')
                ->references('dosen_id')->on('dosens')
                ->nullOnDelete();

            $table->foreign('dosen_pembimbing_id')
                ->references('dosen_id')->on('dosens')
                ->nullOnDelete();

            $table->index(['status', 'nomor_spk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kp_submissions');
    }
};
