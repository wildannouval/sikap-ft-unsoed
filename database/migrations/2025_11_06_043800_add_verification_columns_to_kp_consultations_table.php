<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kp_consultations', function (Blueprint $table) {
            // waktu diverifikasi
            if (!Schema::hasColumn('kp_consultations', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('hasil_konsultasi');
            }

            // dosen yang memverifikasi (FK ke dosens.dosen_id)
            if (!Schema::hasColumn('kp_consultations', 'verified_by_dosen_id')) {
                $table->unsignedBigInteger('verified_by_dosen_id')->nullable()->after('verified_at');

                $table->foreign('verified_by_dosen_id')
                    ->references('dosen_id')->on('dosens')
                    ->nullOnDelete(); // kalau dosen dihapus, set null
            }

            // catatan verifikasi (opsional)
            if (!Schema::hasColumn('kp_consultations', 'verifier_note')) {
                $table->text('verifier_note')->nullable()->after('verified_by_dosen_id');
            }

            // soft deletes jika belum ada (lihat model di bawah)
            if (!Schema::hasColumn('kp_consultations', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kp_consultations', function (Blueprint $table) {
            if (Schema::hasColumn('kp_consultations', 'verifier_note')) {
                $table->dropColumn('verifier_note');
            }
            if (Schema::hasColumn('kp_consultations', 'verified_by_dosen_id')) {
                $table->dropForeign(['verified_by_dosen_id']);
                $table->dropColumn('verified_by_dosen_id');
            }
            if (Schema::hasColumn('kp_consultations', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
            if (Schema::hasColumn('kp_consultations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
