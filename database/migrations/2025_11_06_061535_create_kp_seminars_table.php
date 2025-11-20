<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('kp_seminars')) {
            Schema::create('kp_seminars', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kerja_praktik_id');
                $table->unsignedBigInteger('mahasiswa_id');
                $table->unsignedBigInteger('dosen_pembimbing_id')->nullable();

                // Info pendaftaran dari mahasiswa
                $table->string('judul_laporan')->nullable();
                $table->text('abstrak')->nullable();
                $table->string('berkas_laporan_path')->nullable();

                // Status alur
                $table->enum('status', [
                    'diajukan',
                    'disetujui_pembimbing',
                    'ditolak',
                    'dijadwalkan',
                    'ba_terbit',
                ])->default('diajukan');

                // Jejak approval dosen pembimbing
                $table->timestamp('approved_by_dospem_at')->nullable();
                $table->timestamp('rejected_by_dospem_at')->nullable();
                $table->text('rejected_reason')->nullable();

                // Penjadwalan oleh Bapendik
                $table->dateTime('tanggal_seminar')->nullable();
                $table->unsignedBigInteger('ruangan_id')->nullable();
                $table->string('ruangan_nama')->nullable();

                // BA (dokumen)
                $table->string('nomor_ba')->nullable();
                $table->date('tanggal_ba')->nullable();
                $table->unsignedBigInteger('signatory_id')->nullable();
                $table->string('ttd_signed_by_name')->nullable();
                $table->string('ttd_signed_by_position')->nullable();
                $table->string('ttd_signed_by_nip')->nullable();

                // QR verifikasi BA
                $table->uuid('ba_qr_token')->nullable()->unique();
                $table->timestamp('ba_qr_expires_at')->nullable();

                $table->timestamps();

                // FK
                $table->foreign('kerja_praktik_id')->references('id')->on('kerja_praktiks')->cascadeOnDelete();
                $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswas')->restrictOnDelete();
                $table->foreign('dosen_pembimbing_id')->references('dosen_id')->on('dosens')->nullOnDelete();
                // Jika kamu punya tabel rooms/ruangans, tambah FK ruangan_id di migration terpisah agar fleksibel
            });
        } else {
            // Tabel sudah ada -> opsional tambahkan kolom yang belum ada
            Schema::table('kp_seminars', function (Blueprint $table) {
                // Contoh guard add kolom baru bila belum ada:
                if (! Schema::hasColumn('kp_seminars', 'slides_path')) {
                    $table->string('slides_path')->nullable()->after('berkas_laporan_path');
                }
                if (! Schema::hasColumn('kp_seminars', 'bukti_bimbingan_path')) {
                    $table->string('bukti_bimbingan_path')->nullable()->after('slides_path');
                }

                // Jika di skema lamamu ada nama kolom berbeda, lakukan penyesuaian di migration khusus (perlu doctrine/dbal untuk rename/modify).
            });
        }
    }

    public function down(): void
    {
        // Hati-hati: kalau tabel ini awalnya sudah ada sebelum migration ini,
        // kamu mungkin TIDAK ingin menjatuhkan tabelnya saat rollback.
        // Strategi: bila tabel ada & kolom tambahan tadi ada, drop kolomnya;
        // kalau tabel dibuat oleh migration ini, drop tabelnya.
        if (Schema::hasTable('kp_seminars')) {
            // Coba deteksi: jika ada kolom yang hanya ditambahkan oleh blok "else" di atas,
            // cukup drop kolom; jangan drop tabel.
            $dropOnlyColumns = false;

            if (Schema::hasColumn('kp_seminars', 'slides_path') || Schema::hasColumn('kp_seminars', 'bukti_bimbingan_path')) {
                $dropOnlyColumns = true;
            }

            if ($dropOnlyColumns) {
                Schema::table('kp_seminars', function (Blueprint $table) {
                    if (Schema::hasColumn('kp_seminars', 'slides_path')) {
                        $table->dropColumn('slides_path');
                    }
                    if (Schema::hasColumn('kp_seminars', 'bukti_bimbingan_path')) {
                        $table->dropColumn('bukti_bimbingan_path');
                    }
                });
            } else {
                // kemungkinan besar tabel dibuat oleh migration ini
                Schema::dropIfExists('kp_seminars');
            }
        }
    }
};
