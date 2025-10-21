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
            $table->uuid('uuid')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('lokasi_surat_pengantar',150);
            $table->string('penerima_surat_pengantar',150);
            $table->string('alamat_surat_pengantar',300);
            $table->string('tembusan_surat_pengantar',150)->nullable();
            $table->string('status_surat_pengantar',30)->default('Diajukan');
            $table->date('tanggal_pengajuan_surat_pengantar')->nullable();
            $table->date('tanggal_disetujui_surat_pengantar')->nullable();
            $table->date('tanggal_terbit_surat_pengantar')->nullable();
            $table->date('tanggal_pengambilan_surat_pengantar')->nullable();
            $table->text('catatan_surat')->nullable();
            $table->string('qr_token')->nullable();
            $table->timestamp('qr_expires_at')->nullable();
            $table->timestamp('ttd_signed_at')->nullable();
            $table->unsignedBigInteger('ttd_signed_by')->nullable();
            $table->timestamps();
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
