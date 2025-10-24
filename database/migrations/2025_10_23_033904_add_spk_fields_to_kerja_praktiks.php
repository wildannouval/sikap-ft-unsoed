<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kerja_praktiks', function (Blueprint $table) {
            // Nomor & tanggal terbit SPK
            if (!Schema::hasColumn('kerja_praktiks', 'nomor_spk')) {
                $table->string('nomor_spk')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kerja_praktiks', 'tanggal_terbit_spk')) {
                $table->date('tanggal_terbit_spk')->nullable()->after('nomor_spk');
            }

            // Penandatangan
            if (!Schema::hasColumn('kerja_praktiks', 'signatory_id')) {
                $table->foreignId('signatory_id')->nullable()->after('tanggal_terbit_spk');
                // tambahkan FK hanya saat kolom baru dibuat
                $table->foreign('signatory_id')->references('id')->on('signatories')->nullOnDelete();
            }

            // Snapshot tanda tangan
            if (!Schema::hasColumn('kerja_praktiks', 'ttd_signed_at')) {
                $table->timestamp('ttd_signed_at')->nullable()->after('signatory_id');
            }
            if (!Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_name')) {
                $table->string('ttd_signed_by_name')->nullable()->after('ttd_signed_at');
            }
            if (!Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_position')) {
                $table->string('ttd_signed_by_position')->nullable()->after('ttd_signed_by_name');
            }
            if (!Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_nip')) {
                $table->string('ttd_signed_by_nip')->nullable()->after('ttd_signed_by_position');
            }

            // Token QR verifikasi (unik) – hanya saat belum ada
            if (!Schema::hasColumn('kerja_praktiks', 'spk_qr_token')) {
                $table->string('spk_qr_token', 64)->nullable()->after('ttd_signed_by_nip');
                $table->unique('spk_qr_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kerja_praktiks', function (Blueprint $table) {
            // drop index unik jika ada kolomnya
            if (Schema::hasColumn('kerja_praktiks', 'spk_qr_token')) {
                // nama index unik default: {table}_{column}_unique
                $table->dropUnique('kerja_praktiks_spk_qr_token_unique');
                $table->dropColumn('spk_qr_token');
            }

            if (Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_nip')) {
                $table->dropColumn('ttd_signed_by_nip');
            }
            if (Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_position')) {
                $table->dropColumn('ttd_signed_by_position');
            }
            if (Schema::hasColumn('kerja_praktiks', 'ttd_signed_by_name')) {
                $table->dropColumn('ttd_signed_by_name');
            }
            if (Schema::hasColumn('kerja_praktiks', 'ttd_signed_at')) {
                $table->dropColumn('ttd_signed_at');
            }

            if (Schema::hasColumn('kerja_praktiks', 'signatory_id')) {
                // nama FK default: {table}_{column}_foreign
                $table->dropForeign('kerja_praktiks_signatory_id_foreign');
                $table->dropColumn('signatory_id');
            }

            if (Schema::hasColumn('kerja_praktiks', 'tanggal_terbit_spk')) {
                $table->dropColumn('tanggal_terbit_spk');
            }
            if (Schema::hasColumn('kerja_praktiks', 'nomor_spk')) {
                $table->dropColumn('nomor_spk');
            }
        });
    }
};
