<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_pengantars', function (Blueprint $table) {
            $table->id();

            // metadata & identitas surat
            $table->uuid('uuid')->nullable()->unique();
            $table->string('nomor_surat')->nullable();

            // FK ke mahasiswas(mahasiswa_id) — PERHATIKAN: bukan constrained() default
            $table->unsignedBigInteger('mahasiswa_id');

            $table->string('lokasi_surat_pengantar', 150);
            $table->string('penerima_surat_pengantar', 150);
            $table->string('alamat_surat_pengantar', 300);
            $table->string('tembusan_surat_pengantar', 150)->nullable();

            $table->string('status_surat_pengantar', 30)->default('Diajukan');

            $table->date('tanggal_pengajuan_surat_pengantar')->nullable();
            $table->date('tanggal_disetujui_surat_pengantar')->nullable();
            $table->date('tanggal_terbit_surat_pengantar')->nullable();
            $table->date('tanggal_pengambilan_surat_pengantar')->nullable();

            $table->text('catatan_surat')->nullable();

            // QR & tanda tangan (biarkan apa adanya; ada migrasi snapshot terpisah)
            $table->string('qr_token')->nullable();
            $table->timestamp('qr_expires_at')->nullable();
            $table->timestamp('ttd_signed_at')->nullable();
            $table->unsignedBigInteger('ttd_signed_by')->nullable();

            $table->timestamps();

            // Definisikan FK secara eksplisit karena PK kustom
            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')->on('mahasiswas')
                ->cascadeOnDelete();

            // index tambahan berguna untuk pencarian
            $table->index(['status_surat_pengantar', 'nomor_surat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengantars');
    }
};
