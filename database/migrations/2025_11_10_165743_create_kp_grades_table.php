<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kp_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kp_seminar_id')->index();

            // Komponen Dosen Pembimbing (0..100)
            $table->unsignedTinyInteger('dospem_sistematika_laporan')->default(0);
            $table->unsignedTinyInteger('dospem_tata_bahasa')->default(0);
            $table->unsignedTinyInteger('dospem_sistematika_seminar')->default(0);
            $table->unsignedTinyInteger('dospem_kecocokan_isi')->default(0);
            $table->unsignedTinyInteger('dospem_materi_kp')->default(0);
            $table->unsignedTinyInteger('dospem_penguasaan_masalah')->default(0);
            $table->unsignedTinyInteger('dospem_diskusi')->default(0);

            // Komponen Pembimbing Lapangan (0..100)
            $table->unsignedTinyInteger('pl_kesesuaian')->default(0);
            $table->unsignedTinyInteger('pl_kehadiran')->default(0);
            $table->unsignedTinyInteger('pl_kedisiplinan')->default(0);
            $table->unsignedTinyInteger('pl_keaktifan')->default(0);
            $table->unsignedTinyInteger('pl_kecermatan')->default(0);
            $table->unsignedTinyInteger('pl_tanggung_jawab')->default(0);

            // Rekap
            $table->decimal('score_dospem', 5, 2)->default(0);   // 0..100
            $table->decimal('score_pl', 5, 2)->default(0);       // 0..100
            $table->decimal('final_score', 5, 2)->default(0);    // 0..100
            $table->string('final_letter', 3)->default('D');

            // metadata
            $table->unsignedBigInteger('graded_by_user_id')->nullable(); // dospem yang input
            $table->timestamp('graded_at')->nullable();

            $table->timestamps();

            $table->foreign('kp_seminar_id')->references('id')->on('kp_seminars')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kp_grades');
    }
};
