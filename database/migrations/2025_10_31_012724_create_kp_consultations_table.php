<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kp_consultations', function (Blueprint $table) {
            $table->id();

            // FK ke kerja_praktiks (PK default: id)
            $table->foreignId('kerja_praktik_id')
                ->constrained('kerja_praktiks')
                ->cascadeOnDelete();

            // FK ke mahasiswas (PK kustom: mahasiswa_id)
            $table->unsignedBigInteger('mahasiswa_id');

            // FK ke dosens (PK kustom: dosen_id) — boleh null
            $table->unsignedBigInteger('dosen_pembimbing_id')->nullable();

            // Data konsultasi
            $table->date('tanggal_konsultasi');
            $table->string('topik_konsultasi', 200);
            $table->text('hasil_konsultasi');

            // Verifikasi oleh user dosen (belum diverifikasi = null)
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Index untuk hitung cepat ">= 6 verifikasi"
            $table->index(['kerja_praktik_id', 'verified_at']);

            // ---- Definisi FK manual utk PK kustom ----
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
        Schema::dropIfExists('kp_consultations');
    }
};
